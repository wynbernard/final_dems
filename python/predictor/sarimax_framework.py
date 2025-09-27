import sys, io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding="utf-8")

import pandas as pd
import numpy as np
from statsmodels.tsa.statespace.sarimax import SARIMAX
from sklearn.preprocessing import MinMaxScaler
from datetime import timedelta
from sqlalchemy import create_engine, text
import warnings
warnings.filterwarnings('ignore')


class SarimaxPredictor:
    def __init__(self, db_config):
        self.db_config = db_config
        self.data = None
        self.model_fit = None
        self.scaler = MinMaxScaler(feature_range=(1, 10))
        self.engine = self._create_db_engine()

    def _create_db_engine(self):
        connection_str = (
            f"mysql+pymysql://{self.db_config['user']}:{self.db_config['password']}"
            f"@{self.db_config['host']}/{self.db_config['database']}"
        )
        return create_engine(connection_str)

    def load_data(self, location=None):
        query = """
            SELECT 
                evacuation_location,
                DATE(start_date) AS date,
                SUM(total_evacuation) AS total_evacuation
            FROM evacuation_record_table
            WHERE evacuation_location IS NOT NULL
        """
        if location:
            query += f" AND evacuation_location = '{location}'"
        query += " GROUP BY evacuation_location, DATE(start_date) ORDER BY DATE(start_date)"

        self.data = pd.read_sql(query, self.engine)
        if self.data.empty:
            raise ValueError(f"No data found for location: {location}")

        self.data['date'] = pd.to_datetime(self.data['date'])
        self.data['predictive_score'] = self.scaler.fit_transform(
            self.data[['total_evacuation']]
        ).round(2)
        return self.data

    def fit_model(self, order=(1, 1, 1), seasonal_order=(1, 1, 1, 12)):
        if self.data is None or self.data.empty:
            raise RuntimeError("No data loaded.")
        y = self.data['total_evacuation']
        model = SARIMAX(
            y, order=order, seasonal_order=seasonal_order,
            enforce_stationarity=False, enforce_invertibility=False
        )
        self.model_fit = model.fit(disp=False)

    def forecast(self, steps=1):
        """Forecast only the next N steps (default: 1)."""
        if self.model_fit is None:
            raise RuntimeError("Model not fitted.")
        forecast = self.model_fit.get_forecast(steps=steps)
        last_date = self.data['date'].max()
        dates = [last_date + timedelta(days=i + 1) for i in range(steps)]
        location = self.data['evacuation_location'].iloc[0]

        forecast_df = pd.DataFrame({
            'date': dates,
            'evacuation_location': location,
            'forecast': forecast.predicted_mean,
            'lower_bound': forecast.conf_int()['lower total_evacuation'],
            'upper_bound': forecast.conf_int()['upper total_evacuation']
        })
        forecast_df[['forecast', 'lower_bound', 'upper_bound']] = forecast_df[['forecast', 'lower_bound', 'upper_bound']].clip(lower=0)
        return forecast_df

    def save_multi_scale_forecast(self, forecast_df, location):
        """Save week, month, year forecasts with 3 scaling ranges based on CI."""
        periods = ["WEEK", "MONTH", "YEAR"]

        create_table_query = """
        CREATE TABLE IF NOT EXISTS evacuation_forecasts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            date DATE,
            evacuation_location VARCHAR(255) NOT NULL,
            period VARCHAR(20),
            scale_range VARCHAR(20),
            forecast FLOAT,
            lower_bound FLOAT,
            upper_bound FLOAT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
        """

        delete_query = text("""
            DELETE FROM evacuation_forecasts 
            WHERE evacuation_location = :location
        """)

        insert_query = text("""
            INSERT INTO evacuation_forecasts
            (date, evacuation_location, period, scale_range, forecast, lower_bound, upper_bound)
            VALUES (:date, :evacuation_location, :period, :scale_range, :forecast, :lower_bound, :upper_bound)
        """)

        row = forecast_df.iloc[0]  # use only next event forecast
        mean_val = row['forecast']
        lb = row['lower_bound']
        ub = row['upper_bound']

        with self.engine.begin() as conn:
            conn.execute(text(create_table_query))
            conn.execute(delete_query, {"location": location})

            for period in periods:
                # Different scaling values
                low_forecast = (mean_val + lb) / 2        # pessimistic
                mid_forecast = mean_val                   # expected
                high_forecast = (mean_val + ub) / 2       # optimistic

                for scale, f in [("1-3", low_forecast), ("4-7", mid_forecast), ("8-10", high_forecast)]:
                    conn.execute(insert_query, {
                        "date": row['date'],
                        "evacuation_location": location,
                        "period": period,
                        "scale_range": scale,
                        "forecast": float(f),
                        "lower_bound": float(lb),
                        "upper_bound": float(ub)
                    })

                # Console output
                print(f"✅ {location} → {period} scale 1-3: {low_forecast:.2f} (CI {lb:.2f}–{ub:.2f})")
                print(f"✅ {location} → {period} scale 4-7: {mid_forecast:.2f} (CI {lb:.2f}–{ub:.2f})")
                print(f"✅ {location} → {period} scale 8-10: {high_forecast:.2f} (CI {lb:.2f}–{ub:.2f})")

    def run_forecast_for_location(self, location, steps=1):
        try:
            print(f"\n📍 Running forecast for location: {location}")
            self.load_data(location=location)
            self.fit_model()
            forecast_df = self.forecast(steps=steps)
            self.save_multi_scale_forecast(forecast_df, location)
        except Exception as e:
            print(f"⚠️ Skipped {location}: {e}")

    def run_all_forecasts(self, steps=1):
        query = "SELECT DISTINCT evacuation_location FROM evacuation_record_table WHERE evacuation_location IS NOT NULL"
        locations = pd.read_sql(query, self.engine)['evacuation_location'].tolist()

        print(f"🌐 Running forecasts for all locations...")
        print(f"🔎 Found {len(locations)} locations to forecast.")

        for loc in locations:
            self.run_forecast_for_location(loc, steps=steps)

        print("\n✅ All forecasts completed.")


# -------------------------
# MAIN SCRIPT
# -------------------------
if __name__ == "__main__":
    db_config = {
        "user": "root",
        "password": "",
        "host": "localhost",
        "database": "final_dems"
    }

    predictor = SarimaxPredictor(db_config)
    predictor.run_all_forecasts(steps=1)

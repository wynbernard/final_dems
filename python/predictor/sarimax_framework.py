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

    def load_data(self, barangay=None):
        """Load historical evacuees per barangay from brgy_record_table"""
        query = """
            SELECT 
                barangay_name,
                DATE(date) AS date,
                SUM(total_evacuess) AS total_evacuess
            FROM brgy_record_table
            WHERE barangay_name IS NOT NULL
        """
        if barangay:
            query += " AND barangay_name = :barangay"
        query += " GROUP BY barangay_name, DATE(date) ORDER BY DATE(date)"

        if barangay:
            self.data = pd.read_sql(text(query), self.engine, params={"barangay": barangay})
        else:
            self.data = pd.read_sql(query, self.engine)

        if self.data.empty:
            raise ValueError(f"No data found for barangay: {barangay}")

        self.data['date'] = pd.to_datetime(self.data['date'])

        # scale for reporting
        if 'total_evacuess' in self.data.columns and not self.data['total_evacuess'].isnull().all():
            self.data['predictive_score'] = self.scaler.fit_transform(
                self.data[['total_evacuess']]
            ).round(2)
        else:
            self.data['predictive_score'] = 0
        return self.data

    def fit_model(self, order=(1, 1, 1), seasonal_order=(1, 1, 1, 12)):
        """Fit SARIMAX on total evacuees"""
        if self.data is None or self.data.empty:
            raise RuntimeError("No data loaded.")
        y = self.data['total_evacuess'].astype(float).fillna(0)
        model = SARIMAX(
            y, order=order, seasonal_order=seasonal_order,
            enforce_stationarity=False, enforce_invertibility=False
        )
        self.model_fit = model.fit(disp=False)

    def forecast(self, steps=1):
        """Forecast next N steps"""
        if self.model_fit is None:
            raise RuntimeError("Model not fitted.")
        forecast = self.model_fit.get_forecast(steps=steps)
        last_date = self.data['date'].max()
        dates = [last_date + timedelta(days=i + 1) for i in range(steps)]
        barangay = self.data['barangay_name'].iloc[0]

        ci = forecast.conf_int()
        lower = ci.iloc[:, 0].values
        upper = ci.iloc[:, 1].values

        forecast_df = pd.DataFrame({
            'date': dates,
            'barangay_name': barangay,
            'forecast': np.asarray(forecast.predicted_mean),
            'lower_bound': lower,
            'upper_bound': upper
        })
        forecast_df[['forecast', 'lower_bound', 'upper_bound']] = forecast_df[['forecast', 'lower_bound', 'upper_bound']].clip(lower=0)
        return forecast_df

    def save_multi_scale_forecast(self, forecast_df, barangay):
        """Save forecasts into DB with multiple scales"""
        periods = ["WEEK", "MONTH", "YEAR"]

        create_table_query = """
        CREATE TABLE IF NOT EXISTS brgy_forecasts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            date DATE,
            barangay_name VARCHAR(255) NOT NULL,
            period VARCHAR(20),
            scale_range VARCHAR(20),
            forecast FLOAT,
            lower_bound FLOAT,
            upper_bound FLOAT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
        """

        delete_query = text("""
            DELETE FROM brgy_forecasts 
            WHERE barangay_name = :barangay
        """)

        insert_query = text("""
            INSERT INTO brgy_forecasts
            (date, barangay_name, period, scale_range, forecast, lower_bound, upper_bound)
            VALUES (:date, :barangay_name, :period, :scale_range, :forecast, :lower_bound, :upper_bound)
        """)

        row = forecast_df.iloc[0]
        mean_val = row['forecast']
        lb = row['lower_bound']
        ub = row['upper_bound']

        with self.engine.begin() as conn:
            conn.execute(text(create_table_query))
            conn.execute(delete_query, {"barangay": barangay})

            for period in periods:
                low_forecast = (mean_val + lb) / 2
                mid_forecast = mean_val
                high_forecast = (mean_val + ub) / 2

                for scale, f in [("1-3", low_forecast), ("4-7", mid_forecast), ("8-10", high_forecast)]:
                    conn.execute(insert_query, {
                        "date": row['date'],
                        "barangay_name": barangay,
                        "period": period,
                        "scale_range": scale,
                        "forecast": float(f),
                        "lower_bound": float(lb),
                        "upper_bound": float(ub)
                    })

                print(f"✅ {barangay} → {period} scale 1-3: {low_forecast:.2f} (CI {lb:.2f}–{ub:.2f})")
                print(f"✅ {barangay} → {period} scale 4-7: {mid_forecast:.2f} (CI {lb:.2f}–{ub:.2f})")
                print(f"✅ {barangay} → {period} scale 8-10: {high_forecast:.2f} (CI {lb:.2f}–{ub:.2f})")

    def run_forecast_for_barangay(self, barangay, steps=1):
        try:
            print(f"\n📍 Running forecast for barangay: {barangay}")
            self.load_data(barangay=barangay)
            self.fit_model()
            forecast_df = self.forecast(steps=steps)
            self.save_multi_scale_forecast(forecast_df, barangay)
        except Exception as e:
            print(f"⚠️ Skipped {barangay}: {e}")

    def run_all_forecasts(self, steps=1):
        query = "SELECT DISTINCT barangay_name FROM brgy_record_table WHERE barangay_name IS NOT NULL"
        barangays = pd.read_sql(query, self.engine)['barangay_name'].tolist()

        print(f"🌐 Running forecasts for all barangays...")
        print(f"🔎 Found {len(barangays)} barangays to forecast.")

        for brgy in barangays:
            self.run_forecast_for_barangay(brgy, steps=steps)

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

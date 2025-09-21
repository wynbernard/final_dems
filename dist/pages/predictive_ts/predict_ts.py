#!/usr/bin/env python3
"""
Time-series predictive framework using Prophet + scaling.
- For each evacuation_location in `evacuation_record_table`, builds a time series (ds, y), applies MinMax scaling,
  fits a Prophet model on the scaled target, forecasts the next N periods, inverse-transforms predictions, and
  persists results to `predictive_result`.

Usage:
  python predict_ts.py --periods 7 --freq D

Notes:
- Prophet requires pystan/prophet packages installed; use the `requirements.txt` in this folder.
- The script creates `predictive_result` if missing and truncates it (modify as needed).
"""
import os
import sys
import argparse
from datetime import datetime
import numpy as np
import pandas as pd
from sklearn.preprocessing import MinMaxScaler
from prophet import Prophet
import mysql.connector
from mysql.connector import Error

def safe_print(msg):
    try:
        print(msg)
    except UnicodeEncodeError:
        enc = sys.stdout.encoding or 'utf-8'
        print(msg.encode(enc, errors='replace').decode(enc))

BASE = os.path.dirname(__file__)
OUTPUT = os.path.join(BASE, 'output')
try:
    if not os.path.isdir(OUTPUT):
        os.makedirs(OUTPUT, exist_ok=True)
except Exception as e:
    safe_print(f"[ERROR] Could not create output directory {OUTPUT}: {e}")
    # Re-raise so caller sees the failure
    raise

# Print running environment to help debug "system cannot find the path specified" errors
safe_print(f"BASE={BASE}")
safe_print(f"OUTPUT={OUTPUT}")
safe_print(f"cwd={os.getcwd()}")
safe_print(f"python_executable={sys.executable}")

DB = dict(
    host='srv1322.hstgr.io',
    user='u520834156_userDEMS',
    password='5YnY61~U~Hz',
    database='u520834156_DBDems',
    charset='utf8mb4'
)


# safe_print is defined above


def get_conn():
    try:
        conn = mysql.connector.connect(**DB)
        if conn.is_connected():
            safe_print('[OK] DB connected')
        return conn
    except Error as e:
        safe_print(f'[ERROR] DB connection failed: {e}')
        raise


def fetch_all_records(conn):
    q = """
    SELECT evacuation_location AS barangay, start_date AS ds, total_evacuation AS y
    FROM evacuation_record_table
    WHERE start_date IS NOT NULL
    ORDER BY evacuation_location, start_date ASC
    """
    df = pd.read_sql(q, conn)
    df['ds'] = pd.to_datetime(df['ds'], errors='coerce')
    df = df.dropna(subset=['ds'])
    return df


def fit_and_forecast(ts_df, periods=7, freq='D'):
    # ts_df must have columns ds (datetime), y (numeric)
    ts = ts_df[['ds', 'y']].rename(columns={'ds': 'ds', 'y': 'y'})
    ts = ts.dropna()
    if len(ts) < 3:
        return None

    scaler = MinMaxScaler()
    y_scaled = scaler.fit_transform(ts['y'].values.reshape(-1, 1)).ravel()
    ts_scaled = ts.copy()
    ts_scaled['y'] = y_scaled

    m = Prophet()
    m.fit(ts_scaled)

    future = m.make_future_dataframe(periods=periods, freq=freq)
    fcst = m.predict(future)

    # take the tail periods
    pred_scaled = fcst[['ds', 'yhat']].tail(periods).copy()
    pred_scaled['yhat_inv'] = scaler.inverse_transform(pred_scaled['yhat'].values.reshape(-1, 1)).ravel()
    return pred_scaled[['ds', 'yhat_inv']]


def persist_results(results_df, conn):
    # ensure table
    cur = conn.cursor()
    create = """
    CREATE TABLE IF NOT EXISTS predictive_result (
        id INT AUTO_INCREMENT PRIMARY KEY,
        barangay VARCHAR(255),
        predictive_evacuess INT,
        forecast_date DATE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    """
    cur.execute(create)
    cur.execute('TRUNCATE TABLE predictive_result')

    insert = 'INSERT INTO predictive_result (barangay, predictive_evacuess, forecast_date) VALUES (%s, %s, %s)'
    for _, r in results_df.iterrows():
        cur.execute(insert, (r['barangay'], int(round(r['prediction'])), r['forecast_date'].date()))
    conn.commit()
    cur.close()


def main(argv=None):
    parser = argparse.ArgumentParser()
    parser.add_argument('--periods', type=int, default=7, help='Number of periods to forecast')
    parser.add_argument('--freq', type=str, default='D', help='Frequency string for Prophet (D, W, M)')
    args = parser.parse_args(argv)

    conn = get_conn()
    try:
        df = fetch_all_records(conn)
        if df.empty:
            safe_print('No historical records found')
            return 1

        all_results = []
        for barangay, g in df.groupby('barangay'):
            ts = g[['ds', 'y']].sort_values('ds')
            pred = fit_and_forecast(ts, periods=args.periods, freq=args.freq)
            if pred is None:
                safe_print(f'[WARN] Not enough data for {barangay}, skipping')
                continue
            for _, row in pred.iterrows():
                all_results.append({'barangay': barangay, 'forecast_date': row['ds'], 'prediction': float(row['yhat_inv'])})

        res_df = pd.DataFrame(all_results)
        if res_df.empty:
            safe_print('No predictions generated')
            return 2

        # Save CSV
        ts = datetime.utcnow().strftime('%Y%m%d_%H%M%S')
        out_csv = os.path.join(OUTPUT, f'prophet_predictions_{ts}.csv')
        res_df.to_csv(out_csv, index=False)
        safe_print(f'Predictions written to {out_csv}')

        # Persist
        persist_results(res_df, conn)
        safe_print('Predictions persisted to DB')
        return 0
    except Exception as e:
        safe_print(f'[ERROR] {e}')
        return 3
    finally:
        try:
            conn.close()
        except Exception:
            pass


if __name__ == '__main__':
    sys.exit(main())

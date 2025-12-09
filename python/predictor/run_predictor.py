from sarimax_framework import SarimaxPredictor
import argparse
import os
from dotenv import load_dotenv

def load_db_config():
    """
    Load database configuration from environment variables.
    Looks for .env file in project root, or uses system environment variables.
    """
    # Load .env file from project root (two levels up from this file)
    env_path = os.path.join(os.path.dirname(__file__), '..', '..', '.env')
    load_dotenv(env_path)
    
    # Get credentials from environment variables with fallback defaults
    db_config = {
        "host": os.getenv('PYTHON_DB_HOST') or os.getenv('DB_HOST') or 'srv1322.hstgr.io',
        "user": os.getenv('PYTHON_DB_USER') or os.getenv('DB_USER') or 'u520834156_userDEMS',
        "password": os.getenv('PYTHON_DB_PASS') or os.getenv('DB_PASS') or '5YnY61~U~Hz',
        "database": os.getenv('PYTHON_DB_NAME') or os.getenv('DB_NAME') or 'u520834156_DBDems'
    }
    
    return db_config

def main():
    parser = argparse.ArgumentParser(description='SARIMAX Evacuation Prediction')
    parser.add_argument('--location', type=str, help='Location to analyze (or "ALL" for all locations)')
    parser.add_argument('--days', type=int, default=14, help='Number of days to forecast')
    parser.add_argument('--save-plot', type=str, help='Path to save the forecast plot')
    args = parser.parse_args()

    # Database configuration from environment variables
    db_config = load_db_config()

    try:
        # Initialize predictor
        print("\n🔄 Initializing SARIMAX predictor...")
        predictor = SarimaxPredictor(db_config)

        if args.location and args.location.upper() != "ALL":
            # Run forecast for specific location
            print(f"\n📍 Running forecast for location: {args.location}")
            predictor.run_forecast_for_location(args.location, steps=args.days)
            
            if args.save_plot:
                predictor.plot_forecast(save_path=args.save_plot)
                print(f"📊 Forecast plot saved to: {args.save_plot}")
        else:
            # Run forecasts for all locations
            print("\n🌐 Running forecasts for all locations...")
            predictor.run_all_forecasts(steps=args.days)
            
            if args.save_plot:
                print("\n⚠️ Note: Plot saving is only available for single location forecasts")

    except Exception as e:
        print(f"\n❌ Error: {str(e)}")
        raise

if __name__ == "__main__":
    main()
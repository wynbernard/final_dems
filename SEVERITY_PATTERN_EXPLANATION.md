# Severity Pattern Explanation

## How the Severity/Risk Level System Works

The system categorizes monthly forecasts into **Low**, **Medium**, or **High** risk based on multiple methods that analyze historical data patterns.

---

## Scale Calculation (1-10)

First, the system calculates a **scale value (1-10)** based on evacuation percentage:

### Scale Mapping:
- **Scale 1-3** = **Low Risk** (0-10% of population evacuated)
- **Scale 4-7** = **Medium Risk** (10-30% of population evacuated)
- **Scale 8-10** = **High Risk** (30%+ of population evacuated)

### Detailed Scale Calculation:
```
Evacuation Percentage = (Total Evacuees / Total Population) × 100

If percentage ≤ 5%:     Scale = 1-2
If percentage ≤ 10%:    Scale = 2-3
If percentage ≤ 20%:    Scale = 4-5
If percentage ≤ 30%:    Scale = 6-7
If percentage ≤ 50%:     Scale = 8
If percentage ≤ 70%:     Scale = 9
If percentage > 70%:     Scale = 10
```

---

## Risk Categorization Methods

The system uses **4 methods** (in priority order) to determine risk level:

### **Method 1: Direct Scale Calculation (Primary)**
Uses forecast value and population to calculate scale directly.

**Example:**
- Forecast: 500 evacuees
- Population: 10,000
- Evacuation % = (500/10,000) × 100 = **5%**
- Calculated Scale = **2** (Low Risk)

**Example:**
- Forecast: 3,500 evacuees
- Population: 10,000
- Evacuation % = (3,500/10,000) × 100 = **35%**
- Calculated Scale = **8** (High Risk)

---

### **Method 2: Historical Relationship (Secondary)**
Uses historical patterns between evacuees and scale for that specific month.

**Example for September:**
- Historical September data:
  - Mean evacuees: 1,000
  - Mean scale: 5
  - Scale ratio = 5 / 1,000 = 0.005

- Forecast for October: 1,500 evacuees
- Estimated scale = 1,500 × 0.005 = **7.5** → **8** (High Risk)

---

### **Method 3: Forecast Ratio to Historical Mean (Tertiary)**
Compares forecast to historical mean for that month.

**Example for January:**
- Historical January mean: 800 evacuees
- Forecast: 1,200 evacuees
- Ratio = 1,200 / 800 = **1.5**

- Ratio mapping:
  - Ratio ≤ 0.4 → Scale 1-3 (Low)
  - Ratio 0.4-1.0 → Scale 4-7 (Medium)
  - Ratio > 1.0 → Scale 8-10 (High)

- Since ratio = 1.5 > 1.0:
  - Estimated scale = 8 + ((1.5-1.0)/0.5) × 2 = **10** (High Risk)

---

### **Method 4: Percentile-Based (Fallback)**
Uses statistical percentiles from historical data for that month.

**Example for March:**
- Historical March data: [200, 300, 400, 500, 600, 800, 1000]
- 25th percentile: 300
- 75th percentile: 600
- 90th percentile: 900
- Median: 500
- Mean: 543

- Forecast: 250 evacuees
- Check:
  - ≤ 25th percentile (300)? **Yes** → **Low Risk**
  - ≤ median - 0.5×std? **Yes** → **Low Risk**
  - ≤ mean × 0.4? **Yes** → **Low Risk**

- Forecast: 950 evacuees
- Check:
  - ≥ 90th percentile (900)? **Yes** → **High Risk**
  - ≥ median + 1.0×std? **Yes** → **High Risk**
  - ≥ mean × 1.6? **Yes** → **High Risk**

---

## Complete Example Scenario

### Scenario: Forecasting October for "Abuanan" Barangay

**Historical Data (September only):**
- September records:
  - Record 1: 800 evacuees, 10,000 population → Scale 2 (2% evacuation)
  - Record 2: 1,200 evacuees, 10,000 population → Scale 3 (12% evacuation)
  - Record 3: 1,500 evacuees, 10,000 population → Scale 5 (15% evacuation)
  - Record 4: 2,000 evacuees, 10,000 population → Scale 6 (20% evacuation)

**Statistics:**
- Mean evacuees: 1,375
- Mean scale: 4
- Median scale: 4
- Min scale: 2
- Max scale: 6
- Mean population: 10,000

**Forecast for October:**
- Forecasted evacuees: 1,800

**Risk Calculation (Method 1 - Primary):**
1. Calculate evacuation percentage:
   - (1,800 / 10,000) × 100 = **18%**
2. Calculate scale:
   - 18% falls in 10-20% range → Scale = **5**
3. Compare to historical scale:
   - Scale 5 is between median (4) ± 1.5 → **Medium Risk**

**Result: October = Medium Risk**

---

## Risk Level Summary

| Scale Range | Risk Level | Evacuation % | Meaning |
|------------|------------|--------------|----------|
| 1-3 | **Low** | 0-10% | Minimal impact, normal operations |
| 4-7 | **Medium** | 10-30% | Moderate impact, prepare resources |
| 8-10 | **High** | 30%+ | Severe impact, activate emergency plans |

---

## Key Points

1. **Month-Specific**: The system uses historical data for the **specific month** being forecasted (e.g., October uses October historical data if available, otherwise uses closest month).

2. **Population-Based**: If population data is available, it calculates scale directly from evacuation percentage.

3. **Historical Patterns**: The system learns from past patterns - if a month typically has high evacuations, it adjusts accordingly.

4. **Multiple Methods**: Uses 4 different methods with fallbacks to ensure accuracy even with limited data.

5. **Scale Mapping**: The 1-10 scale directly maps to risk levels:
   - 1-3 = Low
   - 4-7 = Medium  
   - 8-10 = High

---

## Visual Example

```
Barangay: Abuanan
Disaster: Typhoon
Population: 10,000

Historical September Data:
├─ Sep 1: 800 evacuees (8%) → Scale 2 → Low
├─ Sep 5: 1,200 evacuees (12%) → Scale 3 → Low
├─ Sep 10: 1,500 evacuees (15%) → Scale 5 → Medium
└─ Sep 15: 2,000 evacuees (20%) → Scale 6 → Medium

Forecast for October: 1,800 evacuees (18%)
├─ Calculated Scale: 5
├─ Historical Scale Mean: 4
├─ Historical Scale Median: 4
└─ Risk Level: MEDIUM (Scale 5 is within Medium range 4-7)
```

---

## Code Reference

The severity calculation is in:
- `python/predictor/sarimax_framework.py`
- Method: `_categorize_risk_by_month_historical_scale()`
- Lines: 743-888


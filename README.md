# RME EOC Scenario

Zero to Hero Emergency Communications Scenario Generator for Radio Made Easy.

Generates location-customized student task cards and facilitator reference materials for radio emergency communications (emcom) training exercises.

## Setup

```
pip install -r requirements.txt
```

## Usage

```
python zth_generator.py
```

Three modes:
1. **Interactive** -- enter location details at prompts
2. **Fill-in-blank** -- generates templates with blanks for hand-filling
3. **Defaults** -- uses Jackson, Ohio values for fast re-runs

Pre-built location configs are available in `ZTH_Location_Configs.py` for Henderson TX, Stoneboro PA, and Spiro OK.

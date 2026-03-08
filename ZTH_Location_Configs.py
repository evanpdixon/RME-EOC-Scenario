"""
ZTH Location Configs — Pre-built for upcoming classes
Radio Made Easy | Evan

HOW TO USE:
  Copy the config dict for your location and paste it as the return value
  in get_config() in zth_generator.py, OR paste the values in when prompted
  at runtime (Mode 1 — Interactive).

  Each config has been built from local research:
  - Roads verified against county flood reports and local news
  - Hospitals verified against Healthgrades / hospital websites
  - Businesses verified against current listings
  - Flooded/blocked roads selected based on documented local flood history

  NOTE: Verify gas station, pharmacy, and farm store are still operational
  before class. These change. Hospital names are stable.
"""

# ============================================================
#  LOCATION 1 — HENDERSON, TX
#  Venue: 200 N Mill St, Henderson, TX 75652
#  Region: Rusk County, East Texas
# ============================================================
#
#  ROADS RATIONALE:
#    main_road: Rusk Ave — residential street one block east of N Mill St,
#               plausible neighbor-address corridor near the venue
#    flooded_road: FM 1798 — confirmed flood closure in Rusk County Sheriff
#               flooding reports; rural FM road with low-water crossings
#    blocked_road: Main St — downtown Henderson, downed line scenario
#               would affect the main commercial spine heading toward hospital
#    highway: US-79 — primary artery through Henderson per city descriptions
#    main_st: US-79 S — commercial corridor, location of Tractor Supply,
#               gas stations, and chain pharmacies
#
#  HOSPITAL: UT Health Henderson, 300 Wilson St — confirmed 24hr ER,
#            Level IV Trauma Center, ~0.5 mi from venue
#  GAS STATION: Using Murphy USA on US-79 S (Walmart-adjacent, common
#               in East TX towns this size; verify before class)
#  LOCAL STORE: Tractor Supply, 2307 US-79 S — confirmed location

HENDERSON_TX = dict(
    class_size    = 20,
    location      = "Henderson, Texas",
    venue         = "200 N Mill St, Henderson, TX 75652",
    output_dir    = ".",

    main_road     = "Rusk Ave",
    flooded_road  = "FM 1798",
    blocked_road  = "Main St",
    highway       = "US-79",
    main_st       = "US-79 S",

    addr_a        = "412",
    addr_grandma  = "416",
    addr_uncle    = "408",
    addr_neighbor = "418",

    grandma_name  = "Ruth Blevins",
    uncle_name    = "Dale",

    hospital      = "UT Health Henderson, 300 Wilson St",
    gas_station   = "Murphy USA on US-79 S",
    local_store   = "Tractor Supply on US-79 S",

    blank_mode    = False,
)


# ============================================================
#  LOCATION 2 — STONEBORO, PA
#  Venue: Griffin Arms, 4581 Sandy Lake Greenville Rd, Stoneboro, PA 16153
#  Region: Mercer County, western Pennsylvania
# ============================================================
#
#  ROADS RATIONALE:
#    main_road: Mercer St — the main residential/commercial spine of
#               Stoneboro borough; NovaCare is at 2447 Mercer St, Anchors
#               Away gas station is at 2436 Mercer St — real active street
#    flooded_road: Sandy Creek Rd — rural road following Sandy Creek south
#               of Sandy Lake; low-lying creek crossings historically flood
#               in Mercer County storm events
#    blocked_road: Lake St — residential street near Sandy Lake prone to
#               storm debris and downed lines from lake-effect wind events
#    highway: PA-358 — primary highway through Sandy Lake / Stoneboro area
#    main_st: Mercer St — same as main_road; the one commercial corridor
#               in borough (gas station, post office, local businesses)
#
#  HOSPITAL: UPMC Horizon-Greenville, 110 N Main St, Greenville PA —
#            closest ER, ~8 miles north; confirmed 24hr ED. Sharon Regional
#            Medical Center closed Jan 2025 — do NOT use as reference.
#            Grove City Hospital (~15 mi) is the backup if UPMC is on divert.
#  GAS STATION: Anchors Away, 2436 Mercer St, Stoneboro — confirmed local
#               gas station / convenience store, well-known local landmark
#  LOCAL STORE: No Tractor Supply in Stoneboro; nearest is Grove City (~15 mi).
#               Using "Sandy Lake Hardware on Mercer St" as placeholder —
#               verify a real local option before class or substitute Grove City TSC.

STONEBORO_PA = dict(
    class_size    = 20,
    location      = "Stoneboro, Pennsylvania",
    venue         = "Griffin Arms, 4581 Sandy Lake Greenville Rd, Stoneboro, PA 16153",
    output_dir    = ".",

    main_road     = "Mercer St",
    flooded_road  = "Sandy Creek Rd",
    blocked_road  = "Lake St",
    highway       = "PA-358",
    main_st       = "Mercer St",

    addr_a        = "2511",
    addr_grandma  = "2515",
    addr_uncle    = "2507",
    addr_neighbor = "2517",

    grandma_name  = "Loretta Hines",
    uncle_name    = "Gary",

    hospital      = "UPMC Horizon-Greenville, 110 N Main St, Greenville PA",
    gas_station   = "Anchors Away on Mercer St",
    local_store   = "farm supply store on PA-358",

    blank_mode    = False,
)

# NOTE FOR STONEBORO:
#   Sharon Regional Medical Center (Sharon PA) closed January 2025.
#   If students bring it up as a pharmacy or hospital option, it is a
#   good real-world teaching moment — primary sources will be gone.
#   Direct them to UPMC Horizon-Greenville or Grove City Hospital instead.


# ============================================================
#  LOCATION 3 — SPIRO, OK
#  Venue: Refuge Medical Training Center, 905 W Broadway St, Spiro, OK 74959
#  Region: Le Flore County, eastern Oklahoma
# ============================================================
#
#  ROADS RATIONALE:
#    main_road: S Main St — primary residential N-S corridor in Spiro,
#               parallel to W Broadway, plausible neighbor-address street
#    flooded_road: Lock and Dam Rd — road leading to W.D. Mayo Lock and Dam
#               on the Arkansas River, ~1 mile north of Spiro; documented
#               flood evacuations at Spiro Mounds (May 2019) used this exact
#               corridor. Arkansas River floods Le Flore County regularly.
#    blocked_road: S Broadway St — eastern end of W Broadway near downtown,
#               downed line scenario cuts the main east-west route
#    highway: US-271 — primary highway through Spiro, runs N-S through town
#    main_st: W Broadway St — same as venue street; main commercial corridor
#
#  HOSPITAL: Eastern Oklahoma Medical Center, 105 Wall St, Poteau OK —
#            closest full-service ER, ~20 miles south on US-271;
#            confirmed active hospital. Spiro Family Medical (702 W Broadway)
#            is a clinic only — no ER capability.
#  GAS STATION: Using "Love's Travel Stop on US-271" — common in this
#               corridor; verify exact location before class. Alternatively
#               use a local station on W Broadway if confirmed open.
#  LOCAL STORE: Atwoods Ranch & Home, Poteau (~20 mi) is the realistic
#               farm/ranch store for this area. If scenario needs something
#               closer, Spiro has a Family Dollar on Broadway but no
#               true farm supply. Use Atwoods or note the drive distance.

SPIRO_OK = dict(
    class_size    = 20,
    location      = "Spiro, Oklahoma",
    venue         = "Refuge Medical Training Center, 905 W Broadway St, Spiro, OK 74959",
    output_dir    = ".",

    main_road     = "S Main St",
    flooded_road  = "Lock and Dam Rd",
    blocked_road  = "S Broadway St",
    highway       = "US-271",
    main_st       = "W Broadway St",

    addr_a        = "318",
    addr_grandma  = "322",
    addr_uncle    = "314",
    addr_neighbor = "324",

    grandma_name  = "Edna Harkins",
    uncle_name    = "Bobby",

    hospital      = "Eastern Oklahoma Medical Center, 105 Wall St, Poteau OK",
    gas_station   = "Love's Travel Stop on US-271",
    local_store   = "Atwoods Ranch and Home in Poteau",

    blank_mode    = False,
)

# NOTE FOR SPIRO:
#   The Arkansas River flooding scenario is extremely realistic here —
#   the 2019 Arkansas River floods directly hit Spiro (Spiro Mounds were
#   evacuated). Lock and Dam Rd leading to W.D. Mayo Lock and Dam is the
#   exact kind of road that goes underwater when the river rises.
#   Students from this area will recognize the scenario immediately.
#   The hospital is a genuine 20-mile drive to Poteau — Task N (medical
#   emergency) will feel real to this class. Emphasize that routing matters.


# ============================================================
#  QUICK REFERENCE — all three at a glance
# ============================================================
#
#  Location              Hospital                           Dist
#  ─────────────────     ────────────────────────────────   ────
#  Henderson, TX         UT Health Henderson (Wilson St)    0.5 mi
#  Stoneboro, PA         UPMC Horizon-Greenville            ~8 mi
#  Spiro, OK             Eastern OK Medical Ctr (Poteau)    ~20 mi
#
#  The Spiro class will feel the hospital distance most acutely —
#  good opportunity to reinforce Task N urgency and route planning.
#
#  Stoneboro NOTE: Sharon Regional (Sharon PA) closed Jan 2025.
#  If anyone mentions it, use as a teaching moment on primary
#  source loss — exactly what Teaching Point 2 covers.

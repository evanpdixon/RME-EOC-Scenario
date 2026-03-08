#!/usr/bin/env python3
"""
Zero to Hero Scenario Generator
Radio Made Easy  |  Run this script to generate all class materials.

USAGE:
  python zth_generator.py

  Mode 1 - Interactive   : prompts for each location detail
  Mode 2 - Fill-in-blank : prints blanks where location details go
  Mode 3 - Defaults      : uses Jackson, Ohio values (fast re-run)
"""

import os, random, sys
from reportlab.lib.pagesizes import letter
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import inch
from reportlab.lib import colors
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, PageBreak,
    Table, TableStyle, ListFlowable, ListItem,
)
from reportlab.lib.enums import TA_CENTER, TA_RIGHT
from pypdf import PdfWriter, PdfReader

BLANK = "_______________"

# ----------------------------------------------------------------
#  DEFAULT CONFIG  (Stony Point, NC -- RME Headquarters)
# ----------------------------------------------------------------
DEFAULTS = dict(
    class_size    = 20,
    location      = "Stony Point, North Carolina",
    venue         = "8784 NC Highway 90 E, Stony Point, NC 28678",
    output_dir    = ".",

    main_road     = "NC-90",
    flooded_road  = "Rocky Creek Rd",
    blocked_road  = "Wilkesboro Hwy",
    highway       = "I-77",
    main_st       = "NC-90",

    addr_a        = "8780",
    addr_grandma  = "8776",
    addr_uncle    = "8788",
    addr_neighbor = "8774",

    grandma_name  = "Hazel Pittman",
    uncle_name    = "Ray",

    hospital      = "Iredell Memorial Hospital, 557 Brookdale Dr, Statesville NC",
    gas_station   = "BP station on NC-90",
    local_store   = "Tractor Supply on US-21 in Statesville",
)


# ----------------------------------------------------------------
#  CONFIG PROMPT
# ----------------------------------------------------------------
def ask(prompt, default):
    val = input("  %s [%s]: " % (prompt, default)).strip()
    return val if val else default

def get_config():
    print()
    print("=" * 54)
    print("  Zero to Hero Scenario Generator -- Radio Made Easy")
    print("=" * 54)
    print()
    print("  Select mode:")
    print("    1  Interactive   -- enter location details now")
    print("    2  Fill-in-blank -- print blanks, hand-fill later")
    print("    3  Defaults      -- Stony Point, NC HQ (fast re-run)")
    print()

    while True:
        choice = input("  Choice [1/2/3]: ").strip()
        if choice in ("1", "2", "3"):
            break
        print("  Please enter 1, 2, or 3.")

    if choice == "3":
        cfg = dict(DEFAULTS)
        cfg["blank_mode"] = False
        return cfg

    if choice == "2":
        cfg = {k: BLANK for k in DEFAULTS}
        cfg["blank_mode"] = True
        print()
        print("  Fill-in-blank mode. Still need a few settings:")
        print()
        cfg["class_size"] = int(ask("Class size", DEFAULTS["class_size"]))
        cfg["output_dir"] = ask("Output directory", DEFAULTS["output_dir"])
        cfg["location"]   = "Blank_Template"
        return cfg

    # Mode 1 -- Interactive
    print()
    print("  Press Enter to accept the default shown in brackets.")
    print()

    cfg = {"blank_mode": False}

    print("  -- Basic Info --")
    cfg["class_size"] = int(ask("Class size",         DEFAULTS["class_size"]))
    cfg["location"]   = ask("Location (city, state)", DEFAULTS["location"])
    cfg["venue"]      = ask("Venue address",          DEFAULTS["venue"])
    cfg["output_dir"] = ask("Output directory",       DEFAULTS["output_dir"])

    print()
    print("  -- Roads --")
    print("  (main_road is used for all neighbor addresses in Tasks A/C/D)")
    cfg["main_road"]    = ask("Main road name",                        DEFAULTS["main_road"])
    cfg["flooded_road"] = ask("Flooded road (Tasks E/L)",              DEFAULTS["flooded_road"])
    cfg["blocked_road"] = ask("Blocked road / downed lines (K/B/N)",   DEFAULTS["blocked_road"])
    cfg["highway"]      = ask("Nearest major highway",                 DEFAULTS["highway"])
    cfg["main_st"]      = ask("Local commercial street",               DEFAULTS["main_st"])

    print()
    print("  -- Addresses (house numbers on main road) --")
    cfg["addr_a"]        = ask("Task A survivor address #",            DEFAULTS["addr_a"])
    cfg["addr_grandma"]  = ask("Task C grandmother address #",         DEFAULTS["addr_grandma"])
    cfg["addr_uncle"]    = ask("Task D uncle address #",               DEFAULTS["addr_uncle"])
    cfg["addr_neighbor"] = ask("Task C neighbor-of-grandma address #", DEFAULTS["addr_neighbor"])

    print()
    print("  -- People --")
    cfg["grandma_name"] = ask("Grandmother name (Task C)", DEFAULTS["grandma_name"])
    cfg["uncle_name"]   = ask("Uncle name (Task D)",       DEFAULTS["uncle_name"])

    print()
    print("  -- Local Resources --")
    cfg["hospital"]    = ask("Nearest hospital",       DEFAULTS["hospital"])
    cfg["gas_station"] = ask("Gas station reference",  DEFAULTS["gas_station"])
    cfg["local_store"] = ask("Local farm/supply store",DEFAULTS["local_store"])

    print()
    return cfg


# ----------------------------------------------------------------
#  STYLES
# ----------------------------------------------------------------
styles = getSampleStyleSheet()

def sty(name, parent="Normal", **kw):
    return ParagraphStyle(name, parent=styles[parent], **kw)

S_TITLE     = sty("title",   fontSize=20, fontName="Helvetica-Bold",
                  spaceBefore=20, spaceAfter=10, alignment=TA_CENTER)
S_TITLE_RED = sty("title_r", fontSize=20, fontName="Helvetica-Bold",
                  spaceBefore=8,  spaceAfter=10, alignment=TA_CENTER, textColor=colors.red)
S_SUB       = sty("sub",     fontSize=11, fontName="Helvetica",
                  spaceBefore=0,  spaceAfter=16, alignment=TA_CENTER, textColor=colors.grey)
S_BODY      = sty("body",    fontSize=10, fontName="Helvetica",  leading=14, spaceAfter=2)
S_BOLD      = sty("bold",    fontSize=10, fontName="Helvetica-Bold", leading=14, spaceAfter=2)
S_REF       = sty("ref",     fontSize=7,  fontName="Helvetica",
                  textColor=colors.lightgrey, alignment=TA_RIGHT)
S_FAC_H     = sty("fh",      fontSize=13, fontName="Helvetica-Bold", spaceBefore=10, spaceAfter=5)
S_FAC_BODY  = sty("fbody",   fontSize=9,  fontName="Helvetica", leading=13)
S_FAC_NOTE  = sty("fnote",   fontSize=9,  fontName="Helvetica-Oblique",
                  leading=13, textColor=colors.HexColor("#B85C00"))

def bul(text, style=S_BODY):
    return ListItem(Paragraph(text, style), bulletColor=colors.black, leftIndent=16)

def bulleted(items):
    return ListFlowable([bul(i) for i in items],
                        bulletType="bullet", start="\u2022", leftIndent=16)


# ----------------------------------------------------------------
#  TASK DEFINITIONS  (built from cfg at runtime)
# ----------------------------------------------------------------
def build_tasks(cfg):
    m  = cfg["main_road"]
    fr = cfg["flooded_road"]
    br = cfg["blocked_road"]
    hw = cfg["highway"]
    ms = cfg["main_st"]
    aa = "%s %s" % (cfg["addr_a"],        m)
    ag = "%s %s" % (cfg["addr_grandma"],  m)
    au = "%s %s" % (cfg["addr_uncle"],    m)
    an = "%s %s" % (cfg["addr_neighbor"], m)
    gn = cfg["grandma_name"]
    un = cfg["uncle_name"]
    ho = cfg["hospital"]
    gs = cfg["gas_station"]
    ls = cfg["local_store"]
    v  = cfg["venue"]

    return [

        # A
        dict(
            letter="A", phonetic="Alpha", priority=False,
            conflicts=["C", "D"],
            student_tasks=[
                "Establish radio communications with the Emergency Operations Center (EOC) using GMRS Channels 1-22.",
                "Request the current weather forecast and any incoming severe weather warnings.",
                "Your location is: %s." % v,
            ],
            info=[
                "Your address is %s. Your home was unaffected." % aa,
                "Your neighbor at %s evacuated safely before their home was damaged. You have not seen or heard from them since." % ag,
                "Your neighbors at %s are unaffected and safely at home. You can relay messages to or from them if needed." % au,
                "Water in the roadside ditch along %s near %s was rising rapidly this morning." % (m, fr),
                "A church about a half mile south on %s appeared to have lights on -- possibly running on a generator." % m,
                "A utility truck convoy was spotted heading east on %s approximately 2 hours ago." % hw,
            ],
            fac_notes="FEEDS C (grandmother status at %s) and D (uncle status at %s)." % (ag, au),
        ),

        # B
        dict(
            letter="B", phonetic="Bravo", priority=False,
            conflicts=[],
            student_tasks=[
                "Establish radio communications with the Emergency Operations Center (EOC) using GMRS Channels 1-22.",
                "Determine the nearest location where propane filling or exchange is available.",
                "Obtain payment methods, possible driving routes from your location, and cost.",
                "Your location is: %s." % v,
                "You may be asked to use this information in a future scenario -- document it accurately.",
            ],
            info=[
                "Propane exchange racks at the gas station on %s appeared completely empty when you drove past." % ms,
                "You have approximately 3 days of propane remaining -- not an immediate emergency.",
                "A neighbor mentioned that %s was open as of yesterday, but you have not confirmed today." % ls,
                "When you drove through this morning, the %s / %s junction was clear -- no obstructions." % (br, m),
                "%s has debris but is passable in a truck." % m,
                "You have approximately half a tank of gas in your vehicle.",
            ],
            fac_notes="CONTRADICTION with K -- B reports %s clear (earlier this morning); K reports lines now down there." % br,
        ),

        # C
        dict(
            letter="C", phonetic="Charlie", priority=False,
            conflicts=["A", "D"],
            student_tasks=[
                "Establish radio communications with the Emergency Operations Center (EOC) using GMRS Channels 1-22.",
                "Determine the status of your elderly grandmother, %s, who lives at %s. She is 91 years old, lives alone, and you have not heard from her since the disaster." % (gn, ag),
                "If no information is available, leave your contact information and check back regularly.",
                "Your location is: %s." % v,
            ],
            info=[
                "Last contact was 2 days before the disaster -- she was in good health at that time.",
                "She drives a red Ford Ranger, usually parked in the driveway.",
                "She takes daily heart medication and likely has only 3-4 days of supply remaining.",
                "Her neighbor at %s has also not been heard from." % an,
                "She does not have a radio or generator.",
                "You have a spare key to her home if someone is able to check on her.",
            ],
            fac_notes="Task A has direct intel on %s -- A and C must not share a packet." % ag,
        ),

        # D
        dict(
            letter="D", phonetic="Delta", priority=False,
            conflicts=["A", "C"],
            student_tasks=[
                "Establish radio communications with the Emergency Operations Center (EOC) using GMRS Channels 1-22.",
                "Determine the status of your uncle %s who lives at %s with his wife and daughters (ages 16, 13, and 10). You have not heard from them since the disaster." % (un, au),
                "If no information is available, leave your contact information and check back regularly.",
                "Your location is: %s." % v,
            ],
            info=[
                "Your uncle's house is on high ground -- flooding is unlikely at %s." % au,
                "His teenage daughter knows basic radio operation.",
                "He has a tractor and spare fuel cans in the barn.",
                "The family was planning to shelter in place before the disaster.",
                "Your uncle is a retired EMT with a first aid kit.",
                "Their home has a woodstove and could potentially shelter additional people.",
            ],
            fac_notes="Task A has direct intel on %s -- A and D must not share a packet." % au,
        ),

        # E
        dict(
            letter="E", phonetic="Echo", priority=False,
            conflicts=[],
            student_tasks=[
                "Establish radio communications with the Emergency Operations Center (EOC) using GMRS Channels 1-22.",
                "Request the location of emergency shelters that accept pets. Obtain passable route information from your location.",
                "Your location is: %s." % v,
                "You may be asked to use this information in a future scenario -- document it thoroughly.",
            ],
            info=[
                "You have 2 dogs and a horse -- you need a facility that can accommodate large animals.",
                "You drove through the %s creek crossing about 90 minutes ago. The water was high but the road was still passable." % fr,
                "You spotted what appeared to be a Red Cross vehicle near the high school.",
                "The fairgrounds had lights and activity last night, possibly running on a generator.",
                "You have a horse trailer available and could assist with other animal transport if needed.",
                "A neighbor mentioned hearing there may be a shelter at the National Guard armory -- unconfirmed.",
            ],
            fac_notes="CONTRADICTION with L -- Echo: %s passable 90 min ago. Lima: now completely flooded. EOC must reconcile; Lima is more recent." % fr,
        ),

        # F
        dict(
            letter="F", phonetic="Foxtrot", priority=False,
            conflicts=["J"],
            student_tasks=[
                "Establish radio communications with the Emergency Operations Center (EOC) using GMRS Channels 1-22.",
                "Request programming information for the 3 nearest amateur radio repeaters in your area.",
                "Your location is: %s." % v,
                "You may be asked to use this information in a future scenario -- document it accurately so you can program them.",
            ],
            info=[
                "You drove past a chain pharmacy on %s -- staff confirmed completely out of antibiotics." % ms,
                "A nearby pharmacy (large retailer) appeared to be open with a line of cars at the drive-through.",
                "A neighbor who is a ham radio operator mentioned that a local repeater is running on emergency power.",
                "All traffic lights in the area appear to be dark.",
                "The fire department on %s appeared fully staffed with trucks staged outside." % ms,
                "You saw a generator truck parked outside the courthouse.",
            ],
            fac_notes="OVERLAPS J -- both carry pharmacy antibiotic intel. F and J must not share a packet.",
        ),

        # G
        dict(
            letter="G", phonetic="Golf", priority=False,
            conflicts=[],
            student_tasks=[
                "Establish radio communications with the Emergency Operations Center (EOC) using GMRS Channels 1-22.",
                "Request the nearest location with livestock feed available. Determine feed types, payment methods, and passable routes.",
                "Your location is: %s." % v,
                "You may be asked to use this information in a future scenario -- document it accurately.",
            ],
            info=[
                "The %s appeared completely dark and closed when you passed earlier." % gs,
                "%s had a half-full parking lot and appeared to be open." % ls,
                "A neighbor has extra hay bales they are willing to share at no cost.",
                "%s at the creek crossing was completely flooded and impassable as of about an hour ago." % fr,
                "You spotted a utility crew staging near the %s / %s interchange." % (m, hw),
                "You have a flatbed truck available if large quantities of feed need to be transported.",
            ],
            fac_notes="CONTRADICTION with P -- G: %s dark/closed. P: cars lined up. EOC must call to confirm." % gs,
        ),

        # H
        dict(
            letter="H", phonetic="Hotel", priority=False,
            conflicts=[],
            student_tasks=[
                "Establish radio communications with the Emergency Operations Center (EOC) using GMRS Channels 1-22.",
                "Pass a message to your employer outside the disaster zone -- you are safe but unable to come to work due to hazardous conditions. Include something beyond your name to identify yourself. Request confirmation the message was received.",
                "Your location is: %s." % v,
                "This message will actually be passed -- please use real information. You may use the Radio Made Easy phone number from your homework if you prefer.",
            ],
            info=[
                "Propane exchange racks at the gas station on %s were completely empty." % ms,
                "A neighbor told you that some propane filling stations are still operational -- cash only, one tank per person.",
                "Your neighborhood has significant tree debris but roads are passable.",
                "You observed a chainsaw crew actively clearing %s near the %s junction." % (m, hw),
                "You plan to shelter in place for at least the next 48 hours.",
                "You have a portable hand-crank radio and will continue monitoring the emergency frequency.",
            ],
            fac_notes="Provides useful propane context that confirms and adds detail to Task B's request.",
        ),

        # I
        dict(
            letter="I", phonetic="India", priority=False,
            conflicts=[],
            student_tasks=[
                "Establish radio communications with the Emergency Operations Center (EOC) using GMRS Channels 1-22.",
                "Pass a message to a loved one outside the disaster zone informing them of your location and status. Request confirmation from the EOC that the message was delivered.",
                "Ask the EOC to provide some form of authentication from your loved one (a safe word, inside joke, etc.) so you know the message reached the right person.",
                "Your location is: %s." % v,
                "This message will actually be passed -- use real information. The EOC will clearly identify this as a training exercise.",
            ],
            info=[
                "Your home is structurally sound -- only minor basement flooding.",
                "You have food and water for approximately 4 days.",
                "You heard sirens heading north approximately 30 minutes ago.",
                "A neighbor told you a local bridge is intact and passable.",
                "You have a hand-crank radio and have been monitoring the local AM station for updates.",
                "You spotted a National Guard vehicle heading toward town on %s." % hw,
            ],
            fac_notes="",
        ),

        # J
        dict(
            letter="J", phonetic="Juliet", priority=False,
            conflicts=["F", "K"],
            student_tasks=[
                "Establish radio communications with the Emergency Operations Center (EOC) using GMRS Channels 1-22.",
                "Request the location of the nearest pharmacies with antibiotic availability. Confirm operating hours, payment methods, and passable driving routes.",
                "You specifically need antibiotics for a child's ear infection.",
                "Your location is: %s." % v,
                "You may be asked to use this information in a future scenario -- document it accurately.",
            ],
            info=[
                "Your child has had a fever for 2 days -- antibiotics are needed but not immediately life-threatening.",
                "You personally drove to a chain pharmacy and were turned away -- completely out of antibiotics.",
                "A neighbor who just returned from a large retailer said the pharmacy there still has limited stock.",
                "You observed power lines down at %s at the %s junction -- the road appeared partially blocked." % (br, m),
                "You have approximately 2 days of children's Tylenol to manage the fever in the meantime.",
                "Your route into town runs through %s -- the downed lines will affect your travel." % br,
            ],
            fac_notes="OVERLAPS F (antibiotic intel) and K (both observe downed lines at %s). J must not share a packet with either." % br,
        ),

        # K
        dict(
            letter="K", phonetic="Kilo", priority=False,
            conflicts=["J"],
            student_tasks=[
                "Establish radio communications with the Emergency Operations Center (EOC) using GMRS Channels 1-22.",
                "Report downed power lines and outages on your road.",
                "Your location is: %s." % v,
            ],
            info=[
                "Power lines are down at %s at the %s junction -- laying across the westbound lane." % (br, m),
                "You drove through just before the lines came down. The road is now impassable westbound.",
                "A tree is also down on the shoulder of %s, approximately 1 mile south of %s." % (m, hw),
                "No utility crews have been seen in the area yet.",
                "Your neighbor has a whole-home generator and is offering to charge devices for nearby residents.",
                "The %s had lights on and appeared to be operational when you passed." % gs,
            ],
            fac_notes="CONTRADICTION with B -- K: %s now blocked. B: reported it clear this morning. Timeline resolves it (B was earlier). Also FEEDS N -- medical task needs alternate route. K must not share a packet with J." % br,
        ),

        # L
        dict(
            letter="L", phonetic="Lima", priority=False,
            conflicts=[],
            student_tasks=[
                "Establish radio communications with the Emergency Operations Center (EOC) using GMRS Channels 1-22.",
                "Report severe flooding and an impassable road.",
                "Your location: %s at the creek crossing near %s -- water is completely over the road." % (fr, m),
            ],
            info=[
                "The water at the %s creek crossing is completely over the road and appears to still be rising." % fr,
                "You observed a neighbor's livestock loose on %s near the flooding." % m,
                "An elderly man was seen attempting to cross the flooded road on foot -- EOC should be aware.",
                "Bottled water is being distributed at local fire departments.",
                "Your home is on high ground and unaffected.",
                "You have not seen any emergency vehicles or road crews in this area.",
            ],
            fac_notes="CONTRADICTION with E and B -- both reported %s passable earlier. Lima is most recent. Also FEEDS M (fire dept water distribution)." % fr,
        ),

        # M
        dict(
            letter="M", phonetic="Mike", priority=False,
            conflicts=[],
            student_tasks=[
                "Establish radio communications with the Emergency Operations Center (EOC) using GMRS Channels 1-22.",
                "Request information on water distribution locations and water purification methods using common household resources.",
                "Your location is: %s." % v,
                "You may be asked to use this information in a future scenario -- document it accurately.",
            ],
            info=[
                "A neighbor told you that fire departments in the area have bottled water available.",
                "You have a gravity filter and enough supplies for approximately 2 weeks.",
                "%s creek near your location is visibly contaminated with debris and runoff -- do not use untreated." % fr,
                "A neighbor has been collecting rainwater in clean containers as a short-term backup.",
                "Hot meals are reportedly being served at local churches from 7am to 7pm.",
                "Church parking lots on %s had vehicles and activity when you last passed." % ms,
            ],
            fac_notes="",
        ),

        # N -- HIGH PRIORITY
        dict(
            letter="N", phonetic="November", priority=True,
            conflicts=[],
            student_tasks=[
                "Establish radio communications with the Emergency Operations Center (EOC) using GMRS Channels 1-22.",
                "Your father (68) has been experiencing chest pain and left arm numbness for approximately 45 minutes. He is conscious but pale and sweating. You need the fastest passable route to the nearest hospital.",
                "You cannot wait for an ambulance -- you must drive him. Ask the EOC to confirm route conditions and identify any obstructions.",
                "Your location is: %s." % v,
            ],
            info=[
                "Your father took his blood pressure medication this morning.",
                "You have a reliable truck with approximately three-quarters of a tank of gas.",
                "A neighbor has offered their 4WD truck and can drive if needed.",
                "You are aware that %s near the %s junction may have an obstruction -- the EOC should confirm or route around it." % (br, m),
                "You have a basic first aid kit in the truck.",
                "Your father has a history of high blood pressure but no prior cardiac events.",
            ],
            fac_notes="HIGH PRIORITY -- EOC triage target. Route to %s. %s blocked by downed lines (Task K) -- EOC must route around. Watch whether Net Control prioritizes this call correctly." % (ho, br),
        ),

        # O
        dict(
            letter="O", phonetic="Oscar", priority=False,
            conflicts=[],
            student_tasks=[
                "Establish radio communications with the Emergency Operations Center (EOC) using GMRS Channels 1-22.",
                "Request information on safe waste disposal methods -- the sewer system in your area is no longer functioning.",
                "Your location is: %s." % v,
            ],
            info=[
                "All 70cm amateur repeaters in your area appear to be down -- you have tried several.",
                "A neighbor who is a ham radio operator told you there is one 2m repeater on a nearby ridge running on emergency power, but does not know which one.",
                "You have a camp toilet and approximately 1 week of supplies.",
                "A nearby neighbor is burning household trash in a barrel -- raising health concerns.",
                "A noticeable sewer smell is present in the low-lying areas of your neighborhood.",
                "You heard from a neighbor that the local water treatment plant may be offline.",
            ],
            fac_notes="FEEDS F -- Oscar's 2m repeater intel complements Foxtrot's repeater lookup task.",
        ),

        # P
        dict(
            letter="P", phonetic="Papa", priority=False,
            conflicts=[],
            student_tasks=[
                "Establish radio communications with the Emergency Operations Center (EOC) using GMRS Channels 1-22.",
                "Request information on gasoline availability -- location, operating hours, price, payment methods, and route from your location.",
                "Your location is: %s." % v,
                "You may be asked to use this information in a future scenario -- document it accurately.",
            ],
            info=[
                "You saw cars lined up at %s -- it appeared to be operational." % gs,
                "A handwritten sign at the pump stated cash only, 5-gallon limit per vehicle.",
                "The line was approximately 20-30 vehicles when you observed -- expect a significant wait.",
                "The gas station on the highway appeared dark and closed.",
                "You have a 5-gallon gas can and a siphon hose available if needed.",
                "A local bridge you know of is intact and passable.",
            ],
            fac_notes="CONTRADICTION with G -- Papa: %s has cars lined up and operational. Golf: appeared dark and closed. EOC should call to confirm." % gs,
        ),
    ]


def build_contradictions(cfg):
    m  = cfg["main_road"]
    fr = cfg["flooded_road"]
    br = cfg["blocked_road"]
    gs = cfg["gas_station"]
    return [
        ("E <-> L", "%s flooding" % fr,
         "Echo drove through 90 min ago -- high but passable.\n"
         "Lima reports road now completely flooded and rising.\n"
         "EOC must reconcile. Lima is more recent and correct."),
        ("G <-> P", gs,
         "Golf: appeared dark and completely closed.\n"
         "Papa: cars lined up, appeared operational.\n"
         "EOC should attempt to call and confirm before routing anyone there."),
        ("B <-> K", "%s / %s junction" % (br, m),
         "Bravo: drove through this morning -- clear.\n"
         "Kilo: lines came down after Bravo passed -- now blocked westbound.\n"
         "Timeline resolves it. Kilo is more recent. Critical for Task N routing."),
        ("F <-> J", "Antibiotic availability",
         "Foxtrot: chain pharmacies turning people away (confirmed out).\n"
         "Juliet: personally turned away; large retailer may have limited stock.\n"
         "These reinforce each other more than contradict -- EOC should synthesize both."),
    ]


def build_cross_refs(cfg):
    m  = cfg["main_road"]
    ag = "%s %s" % (cfg["addr_grandma"], m)
    au = "%s %s" % (cfg["addr_uncle"],   m)
    return [
        ("A -> C",   "Alpha observes neighbor at %s evacuated safely -- directly answers Charlie's task." % ag),
        ("A -> D",   "Alpha observes neighbor at %s unaffected and home -- directly answers Delta's task." % au),
        ("L -> M",   "Lima mentions fire dept water distribution -- confirms intel needed by Mike."),
        ("O -> F",   "Oscar reports 2m repeater on emergency power -- complements Foxtrot's repeater lookup."),
        ("K/J -> N", "Both Kilo and Juliet observe the blocked road -- EOC must route medical task around this."),
        ("H -> B",   "Hotel confirms propane exchanges empty and filling stations cash-only -- adds detail to Bravo's request."),
    ]


# ----------------------------------------------------------------
#  DISTRIBUTION ALGORITHM
# ----------------------------------------------------------------
def distribute(class_size, tasks, seed=None):
    if seed is not None:
        random.seed(seed)

    task_dict   = {t["letter"]: t for t in tasks}
    priority_t  = [t for t in tasks if t["priority"]]
    normal_t    = [t for t in tasks if not t["priority"]]
    total       = len(tasks)
    assignments = [[] for _ in range(class_size)]

    if class_size <= total:
        max_normal = max(0, class_size - len(priority_t))
        first_pass = priority_t + normal_t[:max_normal]
        for i, task in enumerate(first_pass):
            assignments[i].append(task)
        for task in normal_t[max_normal:]:
            best, best_count = None, 999
            for student_tasks in assignments:
                existing = [t["letter"] for t in student_tasks]
                conflict = any(
                    e in task["conflicts"] or
                    task["letter"] in task_dict[e]["conflicts"]
                    for e in existing
                )
                if not conflict and len(student_tasks) < best_count:
                    best, best_count = student_tasks, len(student_tasks)
            if best is not None:
                best.append(task)
    else:
        all_tasks = priority_t + normal_t
        for i, task in enumerate(all_tasks):
            assignments[i].append(task)
        pool, idx = all_tasks[:], 0
        for si in range(total, class_size):
            assignments[si].append(pool[idx % len(pool)])
            idx += 1

    return assignments


# ----------------------------------------------------------------
#  CARD BUILDER
# ----------------------------------------------------------------
def task_card_elements(task, student_num, cfg):
    elems = []

    if task["priority"]:
        banner = Table([["\u26a0  HIGH PRIORITY -- REPORT IMMEDIATELY TO NET CONTROL  \u26a0"]],
                       colWidths=[6.3 * inch])
        banner.setStyle(TableStyle([
            ("BACKGROUND",    (0,0),(-1,-1), colors.red),
            ("TEXTCOLOR",     (0,0),(-1,-1), colors.white),
            ("FONTNAME",      (0,0),(-1,-1), "Helvetica-Bold"),
            ("FONTSIZE",      (0,0),(-1,-1), 11),
            ("ALIGN",         (0,0),(-1,-1), "CENTER"),
            ("TOPPADDING",    (0,0),(-1,-1), 7),
            ("BOTTOMPADDING", (0,0),(-1,-1), 7),
        ]))
        elems.append(banner)
        elems.append(Spacer(1, 6))

    title_style = S_TITLE_RED if task["priority"] else S_TITLE
    elems.append(Paragraph("Task %s (%s)" % (task["letter"], task["phonetic"]), title_style))
    elems.append(Paragraph(cfg["location"], S_SUB))
    elems.append(Paragraph(
        "You are within the disaster zone without cell phone, internet access, or electricity.",
        S_BODY))
    elems.append(Spacer(1, 6))
    elems.append(Paragraph("Tasks to complete:", S_BOLD))
    elems.append(bulleted(task["student_tasks"]))

    if task["info"]:
        elems.append(Spacer(1, 8))
        elems.append(Paragraph("Information you can provide:", S_BOLD))
        elems.append(bulleted(task["info"]))

    elems.append(Spacer(1, 14))
    elems.append(Paragraph("[S-%02d]" % student_num, S_REF))
    return elems


# ----------------------------------------------------------------
#  FACILITATOR REFERENCE
# ----------------------------------------------------------------
def facilitator_elements(assignments, cfg, contradictions, cross_refs):
    elems = []

    elems.append(Paragraph("Facilitator Reference", S_FAC_H))
    elems.append(Paragraph(
        "%s  |  Class size: %d  |  Tasks distributed: %d" % (
            cfg["location"], len(assignments),
            sum(len(a) for a in assignments)),
        S_FAC_BODY))
    elems.append(Spacer(1, 6))

    pb = Table([["\u26a0  HIGH PRIORITY TASK: N (November) -- Medical Emergency"]],
               colWidths=[6.8 * inch])
    pb.setStyle(TableStyle([
        ("BACKGROUND",    (0,0),(-1,-1), colors.red),
        ("TEXTCOLOR",     (0,0),(-1,-1), colors.white),
        ("FONTNAME",      (0,0),(-1,-1), "Helvetica-Bold"),
        ("FONTSIZE",      (0,0),(-1,-1), 10),
        ("ALIGN",         (0,0),(-1,-1), "CENTER"),
        ("TOPPADDING",    (0,0),(-1,-1), 6),
        ("BOTTOMPADDING", (0,0),(-1,-1), 6),
    ]))
    elems.append(pb)
    elems.append(Paragraph(
        "Watch whether Net Control recognizes and prioritizes this call. "
        "EOC must route around %s / %s downed lines (Task K) to reach the hospital. "
        "The correct answer requires synthesizing intel from K and J." % (
            cfg["blocked_road"], cfg["main_road"]),
        S_FAC_NOTE))
    elems.append(Spacer(1, 10))

    elems.append(Paragraph("Built-in Contradictions", S_FAC_H))
    for pair, topic, detail in contradictions:
        elems.append(Paragraph("<b>%s -- %s</b>" % (pair, topic), S_FAC_BODY))
        for line in detail.split("\n"):
            elems.append(Paragraph(line, S_FAC_NOTE))
        elems.append(Spacer(1, 5))

    elems.append(Spacer(1, 4))
    elems.append(Paragraph("Cross-References", S_FAC_H))
    for ref, desc in cross_refs:
        elems.append(Paragraph("<b>%s</b>  --  %s" % (ref, desc), S_FAC_BODY))
    elems.append(Spacer(1, 12))

    elems.append(Paragraph("Student Assignments", S_FAC_H))
    header = ["#", "Code", "Task(s)", "Notes"]
    rows   = [header]
    for i, student_tasks in enumerate(assignments):
        num      = i + 1
        code     = "S-%02d" % num
        task_str = ",  ".join(
            "%s (%s)%s" % (t["letter"], t["phonetic"], " \u26a0" if t["priority"] else "")
            for t in student_tasks
        )
        notes = "  |  ".join(t["fac_notes"] for t in student_tasks if t.get("fac_notes"))
        rows.append([str(num), code, task_str, notes])

    cell_hdr  = ParagraphStyle("ch",  fontSize=7.5, fontName="Helvetica-Bold",
                                textColor=colors.white, leading=10)
    cell_body = ParagraphStyle("cb",  fontSize=7.5, fontName="Helvetica", leading=10)
    cell_note = ParagraphStyle("cn",  fontSize=6.5, fontName="Helvetica-Oblique",
                                leading=9, textColor=colors.HexColor("#555555"))

    p_rows = [[Paragraph(str(c), cell_hdr) for c in rows[0]]]
    for row in rows[1:]:
        p_rows.append([
            Paragraph(str(row[0]), cell_body),
            Paragraph(str(row[1]), cell_body),
            Paragraph(str(row[2]), cell_body),
            Paragraph(str(row[3]), cell_note),
        ])

    col_w = [0.35*inch, 0.55*inch, 2.1*inch, 4.0*inch]
    grid  = Table(p_rows, colWidths=col_w, repeatRows=1)
    grid.setStyle(TableStyle([
        ("BACKGROUND",    (0,0),(-1, 0), colors.HexColor("#222222")),
        ("ROWBACKGROUNDS",(0,1),(-1,-1), [colors.white, colors.HexColor("#F4F4F4")]),
        ("GRID",          (0,0),(-1,-1), 0.4, colors.grey),
        ("VALIGN",        (0,0),(-1,-1), "TOP"),
        ("TOPPADDING",    (0,0),(-1,-1), 4),
        ("BOTTOMPADDING", (0,0),(-1,-1), 4),
        ("LEFTPADDING",   (0,0),(-1,-1), 4),
        ("RIGHTPADDING",  (0,0),(-1,-1), 4),
    ]))
    elems.append(grid)
    return elems


# ----------------------------------------------------------------
#  PDF BUILDERS
# ----------------------------------------------------------------
COPYRIGHT = (
    "\u00a9 Radio Made Easy. Proprietary training material. "
    "Unauthorized reproduction, distribution, or use outside of "
    "licensed Radio Made Easy courses is strictly prohibited."
)

def _footer(canvas, doc):
    canvas.saveState()
    canvas.setFont("Helvetica", 6.5)
    canvas.setFillColor(colors.HexColor("#888888"))
    canvas.drawCentredString(letter[0] / 2, 0.45 * inch, COPYRIGHT)
    canvas.restoreState()

def build_pdf(filepath, story, margins=(0.85, 0.85, 0.85, 0.85)):
    lm, rm, tm, bm = [m * inch for m in margins]
    doc = SimpleDocTemplate(filepath, pagesize=letter,
                            leftMargin=lm, rightMargin=rm,
                            topMargin=tm, bottomMargin=bm)
    doc.build(story, onFirstPage=_footer, onLaterPages=_footer)

def build_student_pdf(student_num, tasks, cfg, output_dir):
    story = []
    for idx, task in enumerate(tasks):
        story += task_card_elements(task, student_num, cfg)
        if idx < len(tasks) - 1:
            story.append(PageBreak())
    path = os.path.join(output_dir, "Student_%02d.pdf" % student_num)
    build_pdf(path, story)
    return path

def merge_pdfs(output_path, pdf_paths):
    writer = PdfWriter()
    for path in pdf_paths:
        reader = PdfReader(path)
        for page in reader.pages:
            writer.add_page(page)
    with open(output_path, "wb") as f:
        writer.write(f)


# ----------------------------------------------------------------
#  MAIN
# ----------------------------------------------------------------
def main():
    cfg = get_config()

    output_dir = cfg["output_dir"]
    os.makedirs(output_dir, exist_ok=True)

    tasks          = build_tasks(cfg)
    contradictions = build_contradictions(cfg)
    cross_refs     = build_cross_refs(cfg)
    class_size     = cfg["class_size"]
    location       = cfg["location"]
    blank_tag      = "_BLANK" if cfg.get("blank_mode") else ""

    print("\nGenerating scenario for %d students -- %s" % (class_size, location))
    print("-" * 50)

    assignments = distribute(class_size, tasks, seed=42)

    student_paths = []
    for i, student_tasks in enumerate(assignments):
        num   = i + 1
        path  = build_student_pdf(num, student_tasks, cfg, output_dir)
        labels = ", ".join(t["letter"] for t in student_tasks)
        flag   = " (!)" if any(t["priority"] for t in student_tasks) else ""
        print("  Student_%02d.pdf  ->  Tasks: %s%s" % (num, labels, flag))
        student_paths.append(path)

    fac_path  = os.path.join(output_dir, "Facilitator_Reference%s.pdf" % blank_tag)
    fac_story = facilitator_elements(assignments, cfg, contradictions, cross_refs)
    build_pdf(fac_path, fac_story, margins=(0.75, 0.75, 0.75, 0.75))
    print("\n  Facilitator_Reference%s.pdf" % blank_tag)

    loc_slug   = location.replace(", ", "_").replace(" ", "_")
    print_path = os.path.join(output_dir, "PrintAll_%s%s.pdf" % (loc_slug, blank_tag))
    merge_pdfs(print_path, [fac_path] + student_paths)
    print("  PrintAll_%s%s.pdf" % (loc_slug, blank_tag))

    print("\nDone -- %d student files + facilitator + print-all" % len(student_paths))
    print("Output: %s\n" % os.path.abspath(output_dir))


if __name__ == "__main__":
    main()

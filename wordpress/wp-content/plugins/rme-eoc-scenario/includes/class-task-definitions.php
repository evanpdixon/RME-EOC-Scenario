<?php
/**
 * Task definitions — all 16 tasks (A-P), contradictions, and cross-references.
 * Direct port from zth_generator.py build_tasks(), build_contradictions(), build_cross_refs().
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class RME_EOC_Task_Definitions {

    /**
     * Build all 16 tasks from a config array.
     *
     * @param array $cfg Associative array with keys: main_road, flooded_road, blocked_road,
     *                    highway, main_st, addr_a, addr_grandma, addr_uncle, addr_neighbor,
     *                    grandma_name, uncle_name, hospital, gas_station, local_store, venue.
     * @return array Array of task arrays.
     */
    public static function build_tasks( $cfg ) {
        $m  = $cfg['main_road'];
        $fr = $cfg['flooded_road'];
        $br = $cfg['blocked_road'];
        $hw = $cfg['highway'];
        $ms = $cfg['main_st'];
        $aa = $cfg['addr_a'] . ' ' . $m;
        $ag = $cfg['addr_grandma'] . ' ' . $m;
        $au = $cfg['addr_uncle'] . ' ' . $m;
        $an = $cfg['addr_neighbor'] . ' ' . $m;
        $gn = $cfg['grandma_name'];
        $un = $cfg['uncle_name'];
        $ho = $cfg['hospital'];
        $gs = $cfg['gas_station'];
        $ls = $cfg['local_store'];
        $v  = $cfg['venue'];

        return array(

            // A
            array(
                'letter'        => 'A',
                'phonetic'      => 'Alpha',
                'priority'      => false,
                'conflicts'     => array( 'C', 'D' ),
                'student_tasks' => array(
                    'Establish radio communications with the Emergency Operations Center (EOC).',
                    'Request the current weather forecast and any incoming severe weather warnings.',
                ),
                'info' => array(
                    sprintf( 'Your address is %s. Your home was unaffected.', $aa ),
                    sprintf( 'Your neighbor at %s evacuated safely before their home was damaged. You have not seen or heard from them since.', $ag ),
                    sprintf( 'Your neighbors at %s are unaffected and safely at home. You can relay messages to or from them if needed.', $au ),
                    sprintf( 'Water in the roadside ditch along %s near %s was rising rapidly this morning.', $m, $fr ),
                    sprintf( 'A church about a half mile south on %s appeared to have lights on -- possibly running on a generator.', $m ),
                    sprintf( 'A utility truck convoy was spotted heading east on %s approximately 2 hours ago.', $hw ),
                ),
                'fac_notes' => sprintf( 'FEEDS C (grandmother status at %s) and D (uncle status at %s).', $ag, $au ),
            ),

            // B
            array(
                'letter'        => 'B',
                'phonetic'      => 'Bravo',
                'priority'      => false,
                'conflicts'     => array(),
                'student_tasks' => array(
                    'Establish radio communications with the Emergency Operations Center (EOC).',
                    'Determine the nearest location where propane filling or exchange is available.',
                    'Obtain payment methods, possible driving routes from your location, and cost.',
                    sprintf( 'Your location is: %s.', $v ),
                    'You may be asked to use this information in a future scenario -- document it accurately.',
                ),
                'info' => array(
                    sprintf( 'Propane exchange racks at the gas station on %s appeared completely empty when you drove past.', $ms ),
                    'You have approximately 3 days of propane remaining -- not an immediate emergency.',
                    sprintf( 'A neighbor mentioned that %s was open as of yesterday, but you have not confirmed today.', $ls ),
                    sprintf( 'When you drove through this morning, the %s / %s junction was clear -- no obstructions.', $br, $m ),
                    sprintf( '%s has debris but is passable in a truck.', $m ),
                    'You have approximately half a tank of gas in your vehicle.',
                ),
                'fac_notes' => sprintf( 'CONTRADICTION with K -- B reports %s clear (earlier this morning); K reports lines now down there.', $br ),
            ),

            // C
            array(
                'letter'        => 'C',
                'phonetic'      => 'Charlie',
                'priority'      => false,
                'conflicts'     => array( 'A', 'D' ),
                'student_tasks' => array(
                    'Establish radio communications with the Emergency Operations Center (EOC).',
                    sprintf( 'Determine the status of your elderly grandmother, %s, who lives at %s. She is 91 years old, lives alone, and you have not heard from her since the disaster.', $gn, $ag ),
                    'If no information is available, leave your contact information and check back regularly.',
                ),
                'info' => array(
                    'Last contact was 2 days before the disaster -- she was in good health at that time.',
                    'She drives a red Ford Ranger, usually parked in the driveway.',
                    'She takes daily heart medication and likely has only 3-4 days of supply remaining.',
                    sprintf( 'Her neighbor at %s has also not been heard from.', $an ),
                    'She does not have a radio or generator.',
                    'You have a spare key to her home if someone is able to check on her.',
                ),
                'fac_notes' => sprintf( 'Task A has direct intel on %s -- A and C must not share a packet.', $ag ),
            ),

            // D
            array(
                'letter'        => 'D',
                'phonetic'      => 'Delta',
                'priority'      => false,
                'conflicts'     => array( 'A', 'C' ),
                'student_tasks' => array(
                    'Establish radio communications with the Emergency Operations Center (EOC).',
                    sprintf( 'Determine the status of your uncle %s who lives at %s with his wife and daughters (ages 16, 13, and 10). You have not heard from them since the disaster.', $un, $au ),
                    'If no information is available, leave your contact information and check back regularly.',
                ),
                'info' => array(
                    sprintf( 'Your uncle\'s house is on high ground -- flooding is unlikely at %s.', $au ),
                    'His teenage daughter knows basic radio operation.',
                    'He has a tractor and spare fuel cans in the barn.',
                    'The family was planning to shelter in place before the disaster.',
                    'Your uncle is a retired EMT with a first aid kit.',
                    'Their home has a woodstove and could potentially shelter additional people.',
                ),
                'fac_notes' => sprintf( 'Task A has direct intel on %s -- A and D must not share a packet.', $au ),
            ),

            // E
            array(
                'letter'        => 'E',
                'phonetic'      => 'Echo',
                'priority'      => false,
                'conflicts'     => array(),
                'student_tasks' => array(
                    'Establish radio communications with the Emergency Operations Center (EOC).',
                    'Request the location of emergency shelters that accept pets. Obtain passable route information from your location.',
                    'You may be asked to use this information in a future scenario -- document it thoroughly.',
                ),
                'info' => array(
                    'You have 2 dogs and a horse -- you need a facility that can accommodate large animals.',
                    sprintf( 'You drove through the %s creek crossing about 90 minutes ago. The water was high but the road was still passable.', $fr ),
                    'You spotted what appeared to be a Red Cross vehicle near the high school.',
                    'The fairgrounds had lights and activity last night, possibly running on a generator.',
                    'You have a horse trailer available and could assist with other animal transport if needed.',
                    'A neighbor mentioned hearing there may be a shelter at the National Guard armory -- unconfirmed.',
                ),
                'fac_notes' => sprintf( 'CONTRADICTION with L -- Echo: %s passable 90 min ago. Lima: now completely flooded. EOC must reconcile; Lima is more recent.', $fr ),
            ),

            // F
            array(
                'letter'        => 'F',
                'phonetic'      => 'Foxtrot',
                'priority'      => false,
                'conflicts'     => array( 'J' ),
                'student_tasks' => array(
                    'Establish radio communications with the Emergency Operations Center (EOC).',
                    'Request programming information for the 3 nearest amateur radio repeaters in your area.',
                    'You may be asked to use this information in a future scenario -- document it accurately so you can program them.',
                ),
                'info' => array(
                    sprintf( 'You drove past a chain pharmacy on %s -- staff confirmed completely out of antibiotics.', $ms ),
                    'A nearby pharmacy (large retailer) appeared to be open with a line of cars at the drive-through.',
                    'A neighbor who is a ham radio operator mentioned that a local repeater is running on emergency power.',
                    'All traffic lights in the area appear to be dark.',
                    sprintf( 'The fire department on %s appeared fully staffed with trucks staged outside.', $ms ),
                    'You saw a generator truck parked outside the courthouse.',
                ),
                'fac_notes' => 'OVERLAPS J -- both carry pharmacy antibiotic intel. F and J must not share a packet.',
            ),

            // G
            array(
                'letter'        => 'G',
                'phonetic'      => 'Golf',
                'priority'      => false,
                'conflicts'     => array(),
                'student_tasks' => array(
                    'Establish radio communications with the Emergency Operations Center (EOC).',
                    'Request the nearest location with livestock feed available. Determine feed types, payment methods, and passable routes.',
                    sprintf( 'Your location is: %s.', $v ),
                    'You may be asked to use this information in a future scenario -- document it accurately.',
                ),
                'info' => array(
                    sprintf( 'The %s appeared completely dark and closed when you passed earlier.', $gs ),
                    sprintf( '%s had a half-full parking lot and appeared to be open.', $ls ),
                    'A neighbor has extra hay bales they are willing to share at no cost.',
                    sprintf( '%s at the creek crossing was completely flooded and impassable as of about an hour ago.', $fr ),
                    sprintf( 'You spotted a utility crew staging near the %s / %s interchange.', $m, $hw ),
                    'You have a flatbed truck available if large quantities of feed need to be transported.',
                ),
                'fac_notes' => sprintf( 'CONTRADICTION with P -- G: %s dark/closed. P: cars lined up. EOC must call to confirm.', $gs ),
            ),

            // H
            array(
                'letter'        => 'H',
                'phonetic'      => 'Hotel',
                'priority'      => false,
                'conflicts'     => array(),
                'student_tasks' => array(
                    'Establish radio communications with the Emergency Operations Center (EOC).',
                    'Pass a message to your employer outside the disaster zone -- you are safe but unable to come to work due to hazardous conditions. Include something beyond your name to identify yourself. Request confirmation the message was received.',
                    'This message will actually be passed -- please use real information. You may use the Radio Made Easy phone number from your homework if you prefer.',
                ),
                'info' => array(
                    sprintf( 'Propane exchange racks at the gas station on %s were completely empty.', $ms ),
                    'A neighbor told you that some propane filling stations are still operational -- cash only, one tank per person.',
                    'Your neighborhood has significant tree debris but roads are passable.',
                    sprintf( 'You observed a chainsaw crew actively clearing %s near the %s junction.', $m, $hw ),
                    'You plan to shelter in place for at least the next 48 hours.',
                    'You have a portable hand-crank radio and will continue monitoring the emergency frequency.',
                ),
                'fac_notes' => 'Provides useful propane context that confirms and adds detail to Task B\'s request.',
            ),

            // I
            array(
                'letter'        => 'I',
                'phonetic'      => 'India',
                'priority'      => false,
                'conflicts'     => array(),
                'student_tasks' => array(
                    'Establish radio communications with the Emergency Operations Center (EOC).',
                    'Pass a message to a loved one outside the disaster zone informing them of your location and status. Request confirmation from the EOC that the message was delivered.',
                    'Ask the EOC to provide some form of authentication from your loved one (a safe word, inside joke, etc.) so you know the message reached the right person.',
                    'This message will actually be passed -- use real information. The EOC will clearly identify this as a training exercise.',
                ),
                'info' => array(
                    'Your home is structurally sound -- only minor basement flooding.',
                    'You have food and water for approximately 4 days.',
                    'You heard sirens heading north approximately 30 minutes ago.',
                    'A neighbor told you a local bridge is intact and passable.',
                    'You have a hand-crank radio and have been monitoring the local AM station for updates.',
                    sprintf( 'You spotted a National Guard vehicle heading toward town on %s.', $hw ),
                ),
                'fac_notes' => '',
            ),

            // J
            array(
                'letter'        => 'J',
                'phonetic'      => 'Juliet',
                'priority'      => false,
                'conflicts'     => array( 'F', 'K' ),
                'student_tasks' => array(
                    'Establish radio communications with the Emergency Operations Center (EOC).',
                    'Request the location of the nearest pharmacies with antibiotic availability. Confirm operating hours, payment methods, and passable driving routes.',
                    'You specifically need antibiotics for a child\'s ear infection.',
                    sprintf( 'Your location is: %s.', $v ),
                    'You may be asked to use this information in a future scenario -- document it accurately.',
                ),
                'info' => array(
                    'Your child has had a fever for 2 days -- antibiotics are needed but not immediately life-threatening.',
                    'You personally drove to a chain pharmacy and were turned away -- completely out of antibiotics.',
                    'A neighbor who just returned from a large retailer said the pharmacy there still has limited stock.',
                    sprintf( 'You observed power lines down at %s at the %s junction -- the road appeared partially blocked.', $br, $m ),
                    'You have approximately 2 days of children\'s Tylenol to manage the fever in the meantime.',
                    sprintf( 'Your route into town runs through %s -- the downed lines will affect your travel.', $br ),
                ),
                'fac_notes' => sprintf( 'OVERLAPS F (antibiotic intel) and K (both observe downed lines at %s). J must not share a packet with either.', $br ),
            ),

            // K
            array(
                'letter'        => 'K',
                'phonetic'      => 'Kilo',
                'priority'      => false,
                'conflicts'     => array( 'J' ),
                'student_tasks' => array(
                    'Establish radio communications with the Emergency Operations Center (EOC).',
                    'Report downed power lines and outages on your road.',
                ),
                'info' => array(
                    sprintf( 'Power lines are down at %s at the %s junction -- laying across the westbound lane.', $br, $m ),
                    'You drove through just before the lines came down. The road is now impassable westbound.',
                    sprintf( 'A tree is also down on the shoulder of %s, approximately 1 mile south of %s.', $m, $hw ),
                    'No utility crews have been seen in the area yet.',
                    'Your neighbor has a whole-home generator and is offering to charge devices for nearby residents.',
                    sprintf( 'The %s had lights on and appeared to be operational when you passed.', $gs ),
                ),
                'fac_notes' => sprintf( 'CONTRADICTION with B -- K: %s now blocked. B: reported it clear this morning. Timeline resolves it (B was earlier). Also FEEDS N -- medical task needs alternate route. K must not share a packet with J.', $br ),
            ),

            // L
            array(
                'letter'        => 'L',
                'phonetic'      => 'Lima',
                'priority'      => false,
                'conflicts'     => array(),
                'student_tasks' => array(
                    'Establish radio communications with the Emergency Operations Center (EOC).',
                    'Report severe flooding and an impassable road.',
                    sprintf( 'Your location: %s at the creek crossing near %s -- water is completely over the road.', $fr, $m ),
                ),
                'info' => array(
                    sprintf( 'The water at the %s creek crossing is completely over the road and appears to still be rising.', $fr ),
                    sprintf( 'You observed a neighbor\'s livestock loose on %s near the flooding.', $m ),
                    'An elderly man was seen attempting to cross the flooded road on foot -- EOC should be aware.',
                    'Bottled water is being distributed at local fire departments.',
                    'Your home is on high ground and unaffected.',
                    'You have not seen any emergency vehicles or road crews in this area.',
                ),
                'fac_notes' => sprintf( 'CONTRADICTION with E and B -- both reported %s passable earlier. Lima is most recent. Also FEEDS M (fire dept water distribution).', $fr ),
            ),

            // M
            array(
                'letter'        => 'M',
                'phonetic'      => 'Mike',
                'priority'      => false,
                'conflicts'     => array(),
                'student_tasks' => array(
                    'Establish radio communications with the Emergency Operations Center (EOC).',
                    'Request information on water distribution locations and water purification methods using common household resources.',
                    sprintf( 'Your location is: %s.', $v ),
                    'You may be asked to use this information in a future scenario -- document it accurately.',
                ),
                'info' => array(
                    'A neighbor told you that fire departments in the area have bottled water available.',
                    'You have a gravity filter and enough supplies for approximately 2 weeks.',
                    sprintf( '%s creek near your location is visibly contaminated with debris and runoff -- do not use untreated.', $fr ),
                    'A neighbor has been collecting rainwater in clean containers as a short-term backup.',
                    'Hot meals are reportedly being served at local churches from 7am to 7pm.',
                    sprintf( 'Church parking lots on %s had vehicles and activity when you last passed.', $ms ),
                ),
                'fac_notes' => '',
            ),

            // N -- HIGH PRIORITY
            array(
                'letter'        => 'N',
                'phonetic'      => 'November',
                'priority'      => true,
                'conflicts'     => array(),
                'student_tasks' => array(
                    'Establish radio communications with the Emergency Operations Center (EOC).',
                    'Your father (68) has been experiencing chest pain and left arm numbness for approximately 45 minutes. He is conscious but pale and sweating. You need the fastest passable route to the nearest hospital.',
                    'You cannot wait for an ambulance -- you must drive him. Ask the EOC to confirm route conditions and identify any obstructions.',
                ),
                'info' => array(
                    'Your father took his blood pressure medication this morning.',
                    'You have a reliable truck with approximately three-quarters of a tank of gas.',
                    'A neighbor has offered their 4WD truck and can drive if needed.',
                    sprintf( 'You are aware that %s near the %s junction may have an obstruction -- the EOC should confirm or route around it.', $br, $m ),
                    'You have a basic first aid kit in the truck.',
                    'Your father has a history of high blood pressure but no prior cardiac events.',
                ),
                'fac_notes' => sprintf( 'HIGH PRIORITY -- EOC triage target. Route to %s. %s blocked by downed lines (Task K) -- EOC must route around. Watch whether Net Control prioritizes this call correctly.', $ho, $br ),
            ),

            // O
            array(
                'letter'        => 'O',
                'phonetic'      => 'Oscar',
                'priority'      => false,
                'conflicts'     => array(),
                'student_tasks' => array(
                    'Establish radio communications with the Emergency Operations Center (EOC).',
                    'Request information on safe waste disposal methods -- the sewer system in your area is no longer functioning.',
                ),
                'info' => array(
                    'All 70cm amateur repeaters in your area appear to be down -- you have tried several.',
                    'A neighbor who is a ham radio operator told you there is one 2m repeater on a nearby ridge running on emergency power, but does not know which one.',
                    'You have a camp toilet and approximately 1 week of supplies.',
                    'A nearby neighbor is burning household trash in a barrel -- raising health concerns.',
                    'A noticeable sewer smell is present in the low-lying areas of your neighborhood.',
                    'You heard from a neighbor that the local water treatment plant may be offline.',
                ),
                'fac_notes' => 'FEEDS F -- Oscar\'s 2m repeater intel complements Foxtrot\'s repeater lookup task.',
            ),

            // P
            array(
                'letter'        => 'P',
                'phonetic'      => 'Papa',
                'priority'      => false,
                'conflicts'     => array(),
                'student_tasks' => array(
                    'Establish radio communications with the Emergency Operations Center (EOC).',
                    'Request information on gasoline availability -- location, operating hours, price, payment methods, and route from your location.',
                    sprintf( 'Your location is: %s.', $v ),
                    'You may be asked to use this information in a future scenario -- document it accurately.',
                ),
                'info' => array(
                    sprintf( 'You saw cars lined up at %s -- it appeared to be operational.', $gs ),
                    'A handwritten sign at the pump stated cash only, 5-gallon limit per vehicle.',
                    'The line was approximately 20-30 vehicles when you observed -- expect a significant wait.',
                    'The gas station on the highway appeared dark and closed.',
                    'You have a 5-gallon gas can and a siphon hose available if needed.',
                    'A local bridge you know of is intact and passable.',
                ),
                'fac_notes' => sprintf( 'CONTRADICTION with G -- Papa: %s has cars lined up and operational. Golf: appeared dark and closed. EOC should call to confirm.', $gs ),
            ),
        );
    }

    /**
     * Build contradiction list for facilitator reference.
     */
    public static function build_contradictions( $cfg ) {
        $fr = $cfg['flooded_road'];
        $br = $cfg['blocked_road'];
        $m  = $cfg['main_road'];
        $gs = $cfg['gas_station'];

        return array(
            array(
                'pair'   => 'E <-> L',
                'topic'  => sprintf( '%s flooding', $fr ),
                'detail' => "Echo drove through 90 min ago -- high but passable.\nLima reports road now completely flooded and rising.\nEOC must reconcile. Lima is more recent and correct.",
            ),
            array(
                'pair'   => 'G <-> P',
                'topic'  => $gs,
                'detail' => "Golf: appeared dark and completely closed.\nPapa: cars lined up, appeared operational.\nEOC should attempt to call and confirm before routing anyone there.",
            ),
            array(
                'pair'   => 'B <-> K',
                'topic'  => sprintf( '%s / %s junction', $br, $m ),
                'detail' => "Bravo: drove through this morning -- clear.\nKilo: lines came down after Bravo passed -- now blocked westbound.\nTimeline resolves it. Kilo is more recent. Critical for Task N routing.",
            ),
            array(
                'pair'   => 'F <-> J',
                'topic'  => 'Antibiotic availability',
                'detail' => "Foxtrot: chain pharmacies turning people away (confirmed out).\nJuliet: personally turned away; large retailer may have limited stock.\nThese reinforce each other more than contradict -- EOC should synthesize both.",
            ),
        );
    }

    /**
     * Build cross-reference list for facilitator reference.
     */
    public static function build_cross_refs( $cfg ) {
        $m  = $cfg['main_road'];
        $ag = $cfg['addr_grandma'] . ' ' . $m;
        $au = $cfg['addr_uncle'] . ' ' . $m;

        return array(
            array( 'ref' => 'A -> C',   'desc' => sprintf( 'Alpha observes neighbor at %s evacuated safely -- directly answers Charlie\'s task.', $ag ) ),
            array( 'ref' => 'A -> D',   'desc' => sprintf( 'Alpha observes neighbor at %s unaffected and home -- directly answers Delta\'s task.', $au ) ),
            array( 'ref' => 'L -> M',   'desc' => 'Lima mentions fire dept water distribution -- confirms intel needed by Mike.' ),
            array( 'ref' => 'O -> F',   'desc' => 'Oscar reports 2m repeater on emergency power -- complements Foxtrot\'s repeater lookup.' ),
            array( 'ref' => 'K/J -> N', 'desc' => 'Both Kilo and Juliet observe the blocked road -- EOC must route medical task around this.' ),
            array( 'ref' => 'H -> B',   'desc' => 'Hotel confirms propane exchanges empty and filling stations cash-only -- adds detail to Bravo\'s request.' ),
        );
    }
}

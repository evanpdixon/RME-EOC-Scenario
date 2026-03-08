<?php
/**
 * Plain text output generator.
 * Produces AllTasks_[Location].txt matching the existing format.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class RME_EOC_TXT_Generator {

    /**
     * Generate the AllTasks text file.
     *
     * @param array  $cfg         Config array.
     * @param array  $assignments Distribution assignments.
     * @param string $output_dir  Directory to write to.
     * @return string Path to generated file.
     */
    public static function generate( $cfg, $assignments, $output_dir ) {
        $location = $cfg['location'];
        $loc_slug = str_replace( array( ', ', ' ' ), '_', $location );
        $path     = $output_dir . '/AllTasks_' . $loc_slug . '.txt';

        $total_tasks = array_sum( array_map( 'count', $assignments ) );
        $contradictions = RME_EOC_Task_Definitions::build_contradictions( $cfg );
        $cross_refs     = RME_EOC_Task_Definitions::build_cross_refs( $cfg );

        $task_keywords = array(
            'A' => 'Weather / Neighbors',
            'B' => 'Propane',
            'C' => 'Grandmother',
            'D' => 'Uncle & Family',
            'E' => 'Pet Shelter / Livestock Feed',
            'F' => 'Repeaters / Pharmacy',
            'G' => 'Livestock Feed / Gas',
            'H' => 'Employer / Propane',
            'I' => 'Loved One',
            'J' => 'Pharmacy',
            'K' => 'Power Lines',
            'L' => 'Flooding / Water Distribution',
            'M' => 'Water / Hot Meals',
            'N' => 'MEDICAL EMERGENCY',
            'O' => 'Waste Disposal / Repeaters',
            'P' => 'Gasoline',
        );

        $lines = array();

        // --- Facilitator Reference ---
        $lines[] = '================================================';
        $lines[] = 'FACILITATOR REFERENCE';
        $lines[] = $location;
        $lines[] = sprintf( 'Class size: %d  |  Tasks distributed: %d', count( $assignments ), $total_tasks );
        $lines[] = '================================================';
        $lines[] = '';
        $lines[] = 'EOC RADIO INSTRUCTIONS: Monitor and operate on GMRS Channels 15-22 only.';
        $lines[] = 'Students have been instructed to use Channels 1-22.';
        $lines[] = '';
        $lines[] = '!! HIGH PRIORITY TASK: N (November) -- Medical Emergency !!';
        $lines[] = sprintf(
            'Watch whether Net Control recognizes and prioritizes this call. EOC must route around %s / %s downed lines (Task K) to reach the hospital. The correct answer requires synthesizing intel from K and J.',
            $cfg['blocked_road'], $cfg['main_road']
        );
        $lines[] = '';

        // Contradictions
        $lines[] = '--- Built-in Contradictions ---';
        foreach ( $contradictions as $c ) {
            $lines[] = sprintf( '%s -- %s', $c['pair'], $c['topic'] );
            foreach ( explode( "\n", $c['detail'] ) as $line ) {
                $lines[] = '  ' . $line;
            }
        }
        $lines[] = '';

        // Cross-references
        $lines[] = '--- Cross-References ---';
        foreach ( $cross_refs as $cr ) {
            $lines[] = sprintf( '%s -- %s', $cr['ref'], $cr['desc'] );
        }
        $lines[] = '';

        // Assignment table
        $lines[] = '--- Student Assignments ---';
        $lines[] = sprintf( '%-4s %-6s %-20s %-22s %s', '#', 'Code', 'Task(s)', 'Topic', 'Notes' );
        $lines[] = str_repeat( '-', 90 );
        foreach ( $assignments as $i => $student_tasks ) {
            $num  = $i + 1;
            $code = sprintf( 'S-%02d', $num );
            $task_str = implode( ', ', array_map( function( $t ) {
                $flag = $t['priority'] ? ' !!' : '';
                return sprintf( '%s (%s)%s', $t['letter'], $t['phonetic'], $flag );
            }, $student_tasks ) );
            $topics = implode( ', ', array_map( function( $t ) use ( $task_keywords ) {
                return isset( $task_keywords[ $t['letter'] ] ) ? $task_keywords[ $t['letter'] ] : $t['letter'];
            }, $student_tasks ) );
            $notes = implode( ' | ', array_filter( array_map( function( $t ) {
                return $t['fac_notes'];
            }, $student_tasks ) ) );
            $lines[] = sprintf( '%-4d %-6s %-20s %-22s %s', $num, $code, $task_str, $topics, $notes );
        }
        $lines[] = '';

        // --- Blank Task List ---
        $lines[] = '================================================';
        $lines[] = 'TASK LIST (blank -- for EOC use)';
        $lines[] = '================================================';
        foreach ( range( 'A', 'P' ) as $letter ) {
            $lines[] = sprintf( '%s  _________________________________', $letter );
        }
        $lines[] = '';

        // --- EOC Instructions ---
        $lines[] = '================================================';
        $lines[] = 'EMERGENCY OPERATIONS CENTER (EOC)';
        $lines[] = $location;
        $lines[] = '================================================';
        $lines[] = '';
        $lines[] = 'There has been a natural disaster in a nearby area. Utilities, phone, and internet';
        $lines[] = 'are down for anyone inside the disaster zone. You are outside the disaster zone';
        $lines[] = 'with cell phone and internet access. You must stay within this room and assist';
        $lines[] = 'the survivors via radio communication.';
        $lines[] = '';
        $lines[] = '- Establish communications with the disaster survivors via radio.';
        $lines[] = '- Establish an Emergency Communications Net on a single simplex frequency,';
        $lines[] = '  anywhere between GMRS Channel 15 to 22.';
        $lines[] = '- Appoint a team member to serve as Net Control who will organize and';
        $lines[] = '  prioritize the traffic.';
        $lines[] = '- If available, appoint team member(s) to assist with documentation, research,';
        $lines[] = '  and any other necessary tasks. Be sure to keep a thorough written log of';
        $lines[] = '  all communications.';
        $lines[] = '- Use all of your available resources to assist the survivors to complete their';
        $lines[] = '  tasks. Use the Net to request "boots on the ground" information from survivors,';
        $lines[] = '  when applicable. Information found online may not be accurate during a disaster.';
        $lines[] = '  Call to confirm businesses are open, for example.';
        $lines[] = '- Treat all tasks as if they are real (EX: If asked to pass a message, actually';
        $lines[] = '  pass the message).';
        $lines[] = '- When applicable, be sure to inform anyone outside of the scenario this is a';
        $lines[] = '  training exercise.';
        $lines[] = '';

        // --- Student Task Cards ---
        $lines[] = '================================================';
        $lines[] = 'STUDENT TASK CARDS';
        $lines[] = '================================================';

        foreach ( $assignments as $i => $student_tasks ) {
            $num = $i + 1;
            foreach ( $student_tasks as $task ) {
                $lines[] = '';
                $lines[] = '================================================';

                if ( $task['priority'] ) {
                    $lines[] = '!! HIGH PRIORITY -- REPORT IMMEDIATELY TO NET CONTROL !!';
                    $lines[] = '================================================';
                }

                $lines[] = sprintf( '*Task %s (%s)*', $task['letter'], $task['phonetic'] );
                $lines[] = $location;
                $lines[] = '';
                $lines[] = 'You are within the disaster zone without cell phone,';
                $lines[] = 'internet access, or electricity.';
                $lines[] = '';
                $lines[] = 'IMPORTANT: Use ONLY the information provided on this card.';
                $lines[] = 'Do not add to, embellish, invent, or make any other changes';
                $lines[] = 'to this information when communicating with the EOC or other survivors.';
                $lines[] = '';
                $lines[] = '*Tasks to complete:*';
                foreach ( $task['student_tasks'] as $item ) {
                    $lines[] = '- ' . $item;
                }

                if ( ! empty( $task['info'] ) ) {
                    $lines[] = '';
                    $lines[] = '*Information you can provide:*';
                    foreach ( $task['info'] as $item ) {
                        $lines[] = '- ' . $item;
                    }
                }

                $lines[] = '';
                $lines[] = sprintf( '[S-%02d]', $num );
                $lines[] = '================================================';
            }
        }

        $lines[] = '';
        file_put_contents( $path, implode( "\n", $lines ) );
        return $path;
    }
}

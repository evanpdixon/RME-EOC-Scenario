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
        $lines = array();

        $lines[] = '================================================';
        $lines[] = 'ZERO TO HERO -- ALL STUDENT TASKS';
        $lines[] = $location;
        $lines[] = sprintf( 'Class size: %d  |  Tasks distributed: %d', count( $assignments ), $total_tasks );
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

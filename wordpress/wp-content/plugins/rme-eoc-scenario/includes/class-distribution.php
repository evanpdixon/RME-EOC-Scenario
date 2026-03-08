<?php
/**
 * Conflict-aware task distribution algorithm.
 * Direct port from zth_generator.py distribute().
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class RME_EOC_Distribution {

    /**
     * Distribute tasks to students respecting conflict rules.
     *
     * @param int   $class_size Number of students.
     * @param array $tasks      Array of task arrays (each with 'letter', 'conflicts', 'priority').
     * @param int   $seed       Random seed for reproducibility.
     * @return array Array of arrays — each sub-array contains the task(s) assigned to that student.
     */
    public static function distribute( $class_size, $tasks, $seed = 42 ) {
        mt_srand( $seed );

        $task_dict  = array();
        $priority_t = array();
        $normal_t   = array();

        foreach ( $tasks as $task ) {
            $task_dict[ $task['letter'] ] = $task;
            if ( $task['priority'] ) {
                $priority_t[] = $task;
            } else {
                $normal_t[] = $task;
            }
        }

        $total       = count( $tasks );
        $assignments = array();
        for ( $i = 0; $i < $class_size; $i++ ) {
            $assignments[ $i ] = array();
        }

        if ( $class_size <= $total ) {
            $max_normal = max( 0, $class_size - count( $priority_t ) );
            $first_pass = array_merge( $priority_t, array_slice( $normal_t, 0, $max_normal ) );

            foreach ( $first_pass as $i => $task ) {
                $assignments[ $i ][] = $task;
            }

            $leftover = array_slice( $normal_t, $max_normal );
            foreach ( $leftover as $task ) {
                $best       = null;
                $best_count = 999;
                foreach ( $assignments as &$student_tasks ) {
                    $existing = array_map( function( $t ) { return $t['letter']; }, $student_tasks );
                    $conflict = false;
                    foreach ( $existing as $e ) {
                        if (
                            in_array( $e, $task['conflicts'], true ) ||
                            in_array( $task['letter'], $task_dict[ $e ]['conflicts'], true )
                        ) {
                            $conflict = true;
                            break;
                        }
                    }
                    if ( ! $conflict && count( $student_tasks ) < $best_count ) {
                        $best       = &$student_tasks;
                        $best_count = count( $student_tasks );
                    }
                }
                unset( $student_tasks );
                if ( $best !== null ) {
                    $best[] = $task;
                }
                unset( $best );
            }
        } else {
            $all_tasks = array_merge( $priority_t, $normal_t );
            foreach ( $all_tasks as $i => $task ) {
                $assignments[ $i ][] = $task;
            }
            $idx = 0;
            for ( $si = $total; $si < $class_size; $si++ ) {
                $assignments[ $si ][] = $all_tasks[ $idx % count( $all_tasks ) ];
                $idx++;
            }
        }

        return $assignments;
    }
}

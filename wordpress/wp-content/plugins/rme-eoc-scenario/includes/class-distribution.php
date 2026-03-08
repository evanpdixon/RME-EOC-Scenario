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
        // Always assign exactly one unique task per student, up to the number of tasks available.
        // Priority tasks are assigned first, then normal tasks fill remaining slots.
        // Extra students beyond the task count get no card (they staff the EOC).
        $num_field   = min( $class_size, $total );
        $assignments = array();

        $max_normal  = max( 0, $num_field - count( $priority_t ) );
        $all_assigned = array_merge( $priority_t, array_slice( $normal_t, 0, $max_normal ) );

        foreach ( $all_assigned as $task ) {
            $assignments[] = array( $task );
        }

        return $assignments;
    }
}

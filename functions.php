use WPDiscourse\Utilities\Utilities as DiscourseUtilities;

function dcpmp_get_level_for_id( $id ) {
    $levels_to_discourse_groups = array(
        1 => 'group_name'
    );

    if ( empty( $levels_to_discourse_groups[ $id ] ) ) {
      return new WP_Error( 'pmpdc_group_not_set_error', 'A Discourse group has not been assigned to the level.' );
    }

    return $levels_to_discourse_groups[ $id ];
}

function dcpmp_sync_levels( $level_id, $user_id, $cancel_level) {
	if (! empty( $cancel_level ) ) {
		$group_name = dcpmp_get_level_for_id( $cancel_level );
    
		if ( is_wp_error( $group_name ) ) {
			return null;
		}

		$result = DiscourseUtilities::remove_user_from_discourse_group( $user_id, $group_name );
    if ( ! empty( $result->success ) ) {
        // Remove the membership level metadata key.
          delete_user_meta( $user_id, "dcpmp_group_{$group_name}" );
    }

    return $result;
	}
	else {
		$group_name = dcpmp_get_level_for_id( $level_id );
    
		if (is_wp_error($group_name)){
			return null;
		}
    
		$result = DiscourseUtilities::add_user_to_discourse_group($user_id, $group_name);
    
		if ( ! empty( $result->success ) ) {
			// If the user has been added to the group, add a metadata key/value pair that can be used later.
      add_user_meta( $user_id, "dcpmp_group_{$group_name}", 1, true );
    }
    
		return $result;
	}
  
	return new WP_Error( 'dcpmp_membership_not_found_error', 'There was an error syncing group membership with Discourse.');
}

if ( class_exists( '\WPDiscourse\Discourse\Discourse' ) ) {
	add_action( 'pmpro_after_change_membership_level', 'dcpmp_sync_levels', 10, 3 );
}

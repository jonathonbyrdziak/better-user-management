<div class="wrap">
	<div id="icon-users" class="icon32"><br /></div>
	<h2>User Fields</h2>
	
	<div id="col-container">
		<div id="col-right">
			<div class="col-wrap">
				<form id="posts-filter" action="" method="post">
					<div class="tablenav top">
						<div class="tablenav-pages one-page">
							<span class="displaying-num"><?php echo count( $roles->roles ); ?> items</span>
						</div>
					</div>

					<table class="wp-list-table widefat fixed tags" cellspacing="0">
						<thead>
							<tr>
								<th scope='col' id='cb' class='manage-column column-cb check-column'>&nbsp;</th>
								<th scope='col' id='name' class='manage-column column-name'><span>Name</span></th>
								<th scope='col' id='slug' class='manage-column column-slug'><span>Type</span></th>
								<th scope='col' id='posts' class='manage-column column-users num' width="15%"><span>Users</span></th>
							</tr>
						</thead>
					
						<tfoot>
							<tr>
								<th scope='col' id='cb' class='manage-column column-cb check-column'>&nbsp;</th>
								<th scope='col' id='name' class='manage-column column-name'><span>Name</span></th>
								<th scope='col' id='slug' class='manage-column column-slug'><span>Type</span></th>
								<th scope='col' id='posts' class='manage-column column-users num'><span>Users</span></th>
							</tr>
						</tfoot>
					
						<tbody id="the-list" class='list:tag'>
							<?php
							$class = '';
							foreach( $wp_user_fields as $role => $fields ) {
								foreach( $fields as $field_id => $field ) {
									$class = $class == '' ? 'alternate' : '';
									?>
									<tr id="tag-1" class="<?php echo $class; ?>">
										<th scope="row" class="check-column">&nbsp;</th>
										<td class="name column-name">
											<strong><?php echo $field->name; ?></strong><br />
											<div class="row-actions">
												<span class="edit"><a href="users.php?page=manage-roles&edit_id=<?php echo $field; ?>">Edit</a> | </span>
												<span class="trash"><a href="users.php?page=manage-roles&action=delete-user-role&delete-id=<?php echo $key; ?>" class="submitdelete">Trash</a></span>
											</div>
										</td>
										<td class="slug column-slug"><?php echo $field->type; ?></td>
										<td class="posts column-users" align="center"><?php echo count( bum_get_user_by_role( $role['name'] ) ); ?></td>
									</tr>
									<?php
								}
							} ?>
						</tbody>
					</table>
					
					<div class="tablenav bottom">
						<div class="tablenav-pages one-page">
							<span class="displaying-num"><?php echo count( $roles->roles ); ?> items</span>
						</div>
					</div>
					<br class="clear" />
				</form>
			</div>
		</div><!-- /col-right -->
		
		<div id="col-left">
			<div class="col-wrap">
				<div class="form-wrap">
					<h3><?php echo $verb; ?> User Field</h3>
					<form id="addtag" method="post" action="users.php?page=manage-fields" class="validate">
						<input type="hidden" name="action" value="<?php echo $action; ?>" />
						<input type="hidden" name="edit-id" value="<?php echo $edit; ?>" />
						<div class="form-field form-required">
							<label for="field-name">Name</label>
							<input name="field-name" id="field-name" type="text" value="<?php echo $roles->roles[$edit]['name']; ?>" size="40" aria-required="true" />
						</div>
						<div class="form-field">
							<label for="field-type">Type</label>
							<input name="field-type" id="field-type" type="text" value="<?php echo $edit; ?>" />
						</div>
						<div class="form-field">
							<label for="field-roles">Roles</label>
							<select name="field-roles[]" id="field-roles" size="2" multiple="true" style="height:100px;">
								<?php foreach( $roles->roles as $key => $role ) { ?>
								<option value="<?php echo $key; ?>"<?php if( $edit && isset( $capabilities[$edit][$key] ) ) { echo ' selected="selected"'; } ?>><?php echo $role['name']; ?></option>
								<?php } ?>
							</select>
						</div>
						<p class="submit">
							<?php if( $edit ) { ?><a href="users.php?page=manage-fields" class="button" style="float:right;">Cancel</a><?php } ?>
							<input type="submit" name="submit" id="submit" class="button" value="<?php echo $verb; ?> User Field" />
						</p>
					</form>
				</div>
			</div>
		</div><!-- /col-left -->
	</div><!-- /col-container -->
</div>
<div class="wrap">
	<div id="icon-users" class="icon32"><br /></div>
	<h2>User Roles</h2>
	
	<div id="col-container">
		<div id="col-right">
			<div class="col-wrap">
				<form id="posts-filter" action="" method="post">
					<div class="tablenav top">
						<div class="tablenav-pages one-page">
							<span class="displaying-num"><?php echo count( $wp_roles->roles ); ?> items</span>
						</div>
					</div>

					<table class="wp-list-table widefat fixed tags" cellspacing="0">
						<thead>
							<tr>
								<th scope='col' id='cb' class='manage-column column-cb check-column'>&nbsp;</th>
								<th scope='col' id='name' class='manage-column column-name'><span>Name</span></th>
								<th scope='col' id='slug' class='manage-column column-slug'><span>Slug</span></th>
								<th scope='col' id='allow' class='manage-column column-allow' style="text-align:center;"><span>Allow Registration</span></th>
								<th scope='col' id='posts' class='manage-column column-users num' width="15%"><span>Users</span></th>
							</tr>
						</thead>
					
						<tfoot>
							<tr>
								<th scope='col' id='cb' class='manage-column column-cb check-column'>&nbsp;</th>
								<th scope='col' id='name' class='manage-column column-name'><span>Name</span></th>
								<th scope='col' id='slug' class='manage-column column-slug'><span>Slug</span></th>
								<th scope='col' id='allow' class='manage-column column-allow' style="text-align:center;"><span>Allow Registration</span></th>
								<th scope='col' id='posts' class='manage-column column-users num'><span>Users</span></th>
							</tr>
						</tfoot>
					
						<tbody id="the-list" class='list:tag'>
							<?php
							$class = '';
							foreach( $wp_roles->roles as $key => $role ) {
							$class = $class == '' ? 'alternate' : '';
							?>
							<tr id="tag-1" class="<?php echo $class; ?>">
								<th scope="row" class="check-column">&nbsp;</th>
								<td class="name column-name">
									<strong><?php echo $role['name']; ?></strong><br />
									<div class="row-actions">
										<span class="edit"><a href="users.php?page=manage-roles&role-name=<?php echo $key; ?>">Edit</a> | </span>
										<span class="trash"><a href="users.php?page=manage-roles&action=delete-user-role&delete-id=<?php echo $key; ?>" class="submitdelete">Trash</a></span>
									</div>
								</td>
								<td class="slug column-slug"><?php echo $key; ?></td>
								<td class="allow column-allow" align="center"><?php echo $role['register']; ?></td>
								<td class="posts column-users" align="center"><?php echo count( bum_get_user_by_role( $role['name'] ) ); ?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
					
					<div class="tablenav bottom">
						<div class="tablenav-pages one-page">
							<span class="displaying-num"><?php echo count( $wp_roles->roles ); ?> items</span>
						</div>
					</div>
					<br class="clear" />
				</form>
			</div>
		</div><!-- /col-right -->
		
		<div id="col-left">
			<div class="col-wrap">
				<div class="form-wrap">
					<h3><?php echo $verb; ?> User Role</h3>
					<form id="addtag" method="post" action="users.php?page=manage-roles" class="validate">
						<input type="hidden" name="action" value="<?php echo $action; ?>" />
						<input type="hidden" name="edit-id" value="<?php echo $edit; ?>" />
						<div class="form-field form-required">
							<label for="role-name">Name</label>
							<input name="role-name" id="role-name" type="text" value="<?php echo $wp_roles->roles[$edit]['name']; ?>" size="40" aria-required="true" />
						</div>
						<div class="form-field">
							<label for="role-slug">Slug</label>
							<input name="role-slug" id="role-slug" type="text" value="<?php echo $edit; ?>" size="40" />
							<p>The &#8220;slug&#8221; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.</p>
						</div>
						<div class="form-field">
							<label for="role-allow">Allow Registration?</label>
							<input name="role-allow" id="role-allow" type="checkbox" value="yes"<?php if( $wp_roles->roles[$edit]['register'] == 'yes' ) { ?> checked="checked"<?php } ?> style="width:14px;" /> <i>Do you want to allow users to register with this role?</i>
						</div>
						<p class="submit">
							<?php if( $edit ) { ?><a href="users.php?page=manage-roles" class="button" style="float:right;">Cancel</a><?php } ?>
							<input type="submit" name="submit" id="submit" class="button" value="<?php echo $verb; ?> User Role" />
						</p>
					</form>
				</div>
			</div>
		</div><!-- /col-left -->
	</div><!-- /col-container -->
</div>
<style>
#wpbody-content #menu-settings-column {
	display: inline;
	width: 281px;
	margin-left: -300px;
	clear: both;
	float: left;
	padding-top: 24px;
}
#menu-management-liquid {
	float: left;
	min-width: 100%;
}
#nav-menus-frame {
	margin-left: 300px;
}
#menu-management .menu-edit {
	border: #DFDFDF 1px solid;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
	-khtml-border-radius: 3px;
	border-radius: 3px;
	margin-bottom: 20px;
}
#menu-management .nav-tabs-nav {
	margin: 0 20px;
}
.menu-edit > div { padding: 6px; }

#fbMainDiv .widget, #fbMainDiv .widget-top { cursor: pointer !important; }
#fbMainDiv .widget { margin-bottom: 10px; }
#fbMainDiv h4 { margin: 6px 0 6px 6px; }
</style>
<div class="wrap">
	<div id="icon-themes" class="icon32"><br /></div>
	<h2>User Fields</h2>
	
	<div id="nav-menus-frame">
		<div id="menu-settings-column" class="metabox-holder">
			<div id="side-sortables" class="meta-box-sortables">
				<div class="postbox">
					<h3>New Field</h3>
					<div class="inside">
						<p class="howto">Click a field type below to add it.</p>
						<p>
							<div id="form-options"></div>
							<?php
							if( isset( $_GET['ptab'] ) ):
								$term = get_term_by( 'slug', sanitize_title( $_GET['ptab'] ), BUM_HIDDEN_FIELDS );
								?>
								<script type="text/javascript">
								jQuery(document).ready(function(){
								    jQuery('#form-options').formbuilder({
								        'useJson' : true,
								        <?php if( $term->description != '' ) { ?>'load_string': '<?php echo $term->description; ?>'<?php } ?>
								    });
								});
								</script>
							<?php endif; ?>
						</p>
						<p class="button-controls">
							<!-- <input type="button" class="button-primary" value="Add Field" /> -->
						</p>
					</div>
				</div>
			</div>
		</div>
		
		<div id="menu-management-liquid">
			<div id="menu-management">
				<div class="nav-tabs-nav">
					<div class="nav-tabs">
						<?php foreach( $roles->roles as $key => $role ) { ?>
							<?php if( @$_GET['ptab'] == $key ): ?>
							<span class="nav-tab menu-<?php echo $key; ?> nav-tab-active"><?php echo $role['name']; ?></span>
							<?php else: ?>
							<a href="?page=manage-profile&ptab=<?php echo $key; ?>" class="nav-tab menu-<?php echo $key; ?>"><?php echo $role['name']; ?></a>
							<?php endif; ?>
						<?php } ?>
						<a href="?page=manage-roles" class="nav-tab menu-add-new"><abbr title="Add menu">+</abbr></a>
					</div>
				</div>
			
				<div class="menu-edit">
					<div id="nav-menu-header">
						&nbsp;
					</div>
					<div id="post-body">
						<div id="post-body-content">
							Select a role above to edit.
							<ul class="form-builder-container frmb">
							</ul>
						</div>
					</div>
					<div id="nav-menu-footer">
						<div class="major-publishing-actions">
							<div class="publishing-action">
								<form action="#" method="POST">
									<input type="hidden" name="action" value="add_edit" />
									<input type="hidden" name="fbJson" id="fbJsonField" value="" />
									<input type="button" class="button-primary menu-save" id="fbSaveButton" value="Save Profile" />
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="clear"></div>
	</div>
	<!-- 
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
								<th scope='col' id='role' class='manage-column column-role'><span>Role</span></th>
								<th scope='col' id='slug' class='manage-column column-slug'><span>Type</span></th>
								<th scope='col' id='posts' class='manage-column column-users num' width="15%"><span>Users</span></th>
							</tr>
						</thead>
					
						<tfoot>
							<tr>
								<th scope='col' id='cb' class='manage-column column-cb check-column'>&nbsp;</th>
								<th scope='col' id='name' class='manage-column column-name'><span>Name</span></th>
								<th scope='col' id='role' class='manage-column column-role'><span>Role</span></th>
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
												<span class="edit"><a href="users.php?page=manage-fields&edit_id=<?php echo $field_id; ?>">Edit</a> | </span>
												<span class="trash"><a href="users.php?page=manage-fields&action=delete-field&delete-id=<?php echo $field_id; ?>" class="submitdelete">Trash</a></span>
											</div>
										</td>
										<td class="role column-role"><?php echo $role; ?></td>
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
		</div>
		
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
		</div>
	</div>
</div> -->
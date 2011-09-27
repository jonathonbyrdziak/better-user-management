
<div class="wrap">
	<div id="icon-users" class="icon32"><br /></div>
	<h2>User Capabilities</h2>
	
	<div id="col-container">
		<div id="col-right">
			<div class="col-wrap">
				<form id="posts-filter" action="" method="post">
					<div class="tablenav top">
						<div class="tablenav-pages one-page">
							<span class="displaying-num"><?php echo count( $capabilities ); ?> items</span>
						</div>
					</div>
				
					<table class="wp-list-table widefat fixed tags" cellspacing="0">
						<thead>
							<tr>
								<th scope='col' id='cb' class='manage-column column-cb check-column'>&nbsp;</th>
								<th scope='col' id='name' class='manage-column column-capability' width="20%"><span>Capability</span></th>
								<th scope='col' id='posts' class='manage-column column-status num'><span>Roles</span></th>
							</tr>
						</thead>
					
						<tfoot>
							<tr>
								<th scope='col' id='cb' class='manage-column column-cb check-column'>&nbsp;</th>
								<th scope='col' id='name' class='manage-column column-capability'><span>Capability</span></th>
								<th scope='col' id='posts' class='manage-column column-status num'><span>Roles</span></th>
							</tr>
						</tfoot>
					
						<tbody id="the-list" class='list:tag'>
							<?php
							$class = '';
							foreach( $capabilities as $key => $capability ) {
							$class = $class == '' ? 'alternate' : '';
							?>
							<tr id="tag-1" class="<?php echo $class; ?>">
								<th scope="row" class="check-column">&nbsp;</th>
								<td class="name column-name">
									<strong><?php echo $key; ?></strong>
									<div class="row-actions">
										<span class="edit"><a href="users.php?page=manage-capabilities&edit-cap=<?php echo $key; ?>">Edit</a> | </span>
										<span class="trash"><a href="users.php?page=manage-capabilities&action=delete-user-cap&delete-id=<?php echo $key; ?>" class="submitdelete">Trash</a></span>
									</div>
								</td>
								<td class="posts column-users" align="center">
								<?php
								$tmp = '';
								foreach( $roles->roles as $key_2 => $role )
									if( isset( $role['capabilities'][$key] ) )
										$tmp .= $role['name'].', ';
								
								if( $tmp )
									echo substr( $tmp, 0, -2 );
								?>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
					
					<div class="tablenav bottom">
						<div class="tablenav-pages one-page">
							<span class="displaying-num"><?php echo count( $capabilities ); ?> items</span>
						</div>
					</div>
					<br class="clear" />
				</form>
			</div>
		</div><!-- /col-right -->
		
		<div id="col-left">
			<div class="col-wrap">
				<div class="form-wrap">
					<h3><?php echo $verb; ?> User Capability</h3>
					<form id="addtag" method="post" action="users.php?page=manage-capabilities" class="validate">
						<input type="hidden" name="action" value="<?php echo $action; ?>" />
						<input type="hidden" name="edit-id" value="<?php echo $edit; ?>" />
						<div class="form-field form-required">
							<label for="cap-name">Name</label>
							<input name="cap-name" id="cap-name" type="text" value="<?php echo $edit; ?>" size="40" aria-required="true" />
						</div>
						<div class="form-field">
							<label for="cap-roles">Roles</label>
							<select name="cap-roles[]" id="cap-roles" size="2" multiple="true" style="height:100px;">
								<?php foreach( $roles->roles as $key => $role ) { ?>
								<option value="<?php echo $key; ?>"<?php if( $edit && isset( $capabilities[$edit][$key] ) ) { echo ' selected="selected"'; } ?>><?php echo $role['name']; ?></option>
								<?php } ?>
							</select>
						</div>
						<p class="submit">
							<?php if( $edit ) { ?><a href="users.php?page=manage-capabilities" class="button" style="float:right;">Cancel</a><?php } ?>
							<input type="submit" name="submit" id="submit" class="button" value="<?php echo $verb; ?> User Capability" />
						</p>
					</form>
				</div>
			</div>
		</div><!-- /col-left -->
	</div><!-- /col-container -->
</div>
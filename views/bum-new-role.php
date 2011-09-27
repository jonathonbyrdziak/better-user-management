<div class="wrap">
	<div id="icon-users" class="icon32"><br /></div>
	<h2>New Custom User Role</h2>
	<p>Create a new custom user role and then you can create custom fields for registration and user profile.</p>
	
	<form action="users.php?page=custom-user-roles&action=<?php echo $action; ?>" method="POST">
		<table class="form-table" style="width:40%;">
			<tbody>
				<tr class="form-field">
					<th scope="row"><label for="name">Name</label></th>
					<td><input name="name" type="text" id="name" value="<?php if( isset( $data ) ) { echo $data->name; } ?>" /></td>
				</tr>
				<tr class="form-field">
					<th scope="row"><label for="description">Description</label></th>
					<td><textarea name="description"><?php if( isset( $data ) ) { echo $data->description; } ?></textarea></td>
				</tr>
			</tbody>
		</table>
		<input type="hidden" name="edit_id" value="<?php echo $id; ?>" />
		<p class="submit"><input type="submit" class="button-primary" value="<?php echo $verb; ?> User Role" /></p>
	</form>
</div>
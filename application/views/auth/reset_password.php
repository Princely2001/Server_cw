<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? html_escape($title) : 'Reset Password'; ?></title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 50px; }
        .container { max-width: 400px; margin: auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 8px; box-sizing: border-box; }
        .btn { width: 100%; padding: 10px; background-color: #0056b3; color: #fff; border: none; border-radius: 3px; cursor: pointer; }
        .btn:hover { background-color: #004494; }
        .error { color: red; margin-bottom: 15px; font-size: 0.9em; }
    </style>
</head>
<body>

<div class="container">
    <h2>Create New Password</h2>

    <?php if(validation_errors()): ?>
        <div class="error">
            <?php echo validation_errors(); ?>
        </div>
    <?php endif; ?>

    <?php echo form_open('auth/reset_password'); ?>

        <input type="hidden" name="token" value="<?php echo html_escape($token); ?>">

        <div class="form-group">
            <label for="password">New Password</label>
            <input type="password" name="password" id="password" required>
            <small>Must contain uppercase, lowercase, number, and special character (Min 8 chars).</small>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
        </div>

        <button type="submit" class="btn">Reset Password</button>

    <?php echo form_close(); ?>
</div>

</body>
</html>
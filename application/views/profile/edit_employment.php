<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= html_escape($title) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; max-width: 600px; margin: auto; padding-top: 50px;}
        .section { border: 1px solid #ccc; padding: 20px; border-radius: 5px; background: #fff; }
        .alert-danger { background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; margin-bottom: 15px; }
        label { font-weight: bold; display: block; margin-top: 15px;}
        input[type="text"], input[type="url"], input[type="date"] { width: 100%; padding: 10px; margin-top: 5px; box-sizing: border-box; }
        button { margin-top: 20px; padding: 10px 15px; background: #28a745; color: white; border: none; cursor: pointer; border-radius: 3px;}
        button:hover { background: #218838; }
        .cancel-link { margin-left: 15px; color: #dc3545; text-decoration: none; }
    </style>
</head>
<body style="background-color: #f4f4f4;">

    <div class="section">
        <h2>Edit Employment History</h2>

        <?php if (validation_errors()): ?>
            <div class="alert-danger"><?= validation_errors(); ?></div>
        <?php endif; ?>

        <?= form_open('profile/edit_employment/'.$employment->id) ?>
            <label>Company Name</label>
            <input type="text" name="company_name" value="<?= html_escape($employment->company_name) ?>" required>

            <label>Job Role</label>
            <input type="text" name="role" value="<?= html_escape($employment->role) ?>" required>

            <label>Start Date</label>
            <input type="date" name="start_date" value="<?= html_escape($employment->start_date) ?>" required>

            <label>End Date (Leave blank if currently working here)</label>
            <input type="date" name="end_date" value="<?= html_escape($employment->end_date) ?>">

            <button type="submit">Update Employment</button>
            <a href="<?= site_url('profile/index') ?>" class="cancel-link">Cancel</a>
        <?= form_close() ?>
    </div>

</body>
</html>
<div class="wrap pet-shop-manager">
    <h1><?php _e('Data Management', 'pet-shop-manager'); ?></h1>
    <p><?php _e('Manage your data by exporting and importing product, sales, and expense information.', 'pet-shop-manager'); ?></p>

    <div class="card">
        <h2><?php _e('Export Data', 'pet-shop-manager'); ?></h2>
        <p><?php _e('Export your data to a JSON file for backup.', 'pet-shop-manager'); ?></p>
        <button id="export-products-btn" class="button button-primary"><?php _e('Export Product List Only', 'pet-shop-manager'); ?></button>
        <button id="export-all-btn" class="button button-primary"><?php _e('Export All Data', 'pet-shop-manager'); ?></button>
    </div>

    <div class="card">
        <h2><?php _e('Import Data', 'pet-shop-manager'); ?></h2>
        <p style="color: var(--danger-color);"><?php _e('Warning: Importing data will overwrite existing data.', 'pet-shop-manager'); ?></p>
        <div class="form-group">
            <label for="import-products-file"><?php _e('Import Product List Only', 'pet-shop-manager'); ?></label>
            <input type="file" id="import-products-file" accept=".json">
        </div>
        <div class="form-group">
            <label for="import-all-file"><?php _e('Import All Data', 'pet-shop-manager'); ?></label>
            <input type="file" id="import-all-file" accept=".json">
        </div>
    </div>
</div>
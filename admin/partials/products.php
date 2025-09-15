<div class="wrap pet-shop-manager">
    <h1><?php _e('Product Management', 'pet-shop-manager'); ?></h1>
    <div id="product-management">
        <button id="add-product" class="button button-primary"><?php _e('Add New Product', 'pet-shop-manager'); ?></button>
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php _e('Product Name', 'pet-shop-manager'); ?></th>
                    <th><?php _e('Buying Price', 'pet-shop-manager'); ?></th>
                    <th><?php _e('Actions', 'pet-shop-manager'); ?></th>
                </tr>
            </thead>
            <tbody id="product-list">
                <!-- Product rows will be dynamically inserted here -->
            </tbody>
        </table>
    </div>
</div>

<div id="product-modal" style="display:none;" class="modal">
    <div class="modal-content">
        <h2><?php _e('Product Details', 'pet-shop-manager'); ?></h2>
        <form id="product-form">
            <input type="hidden" id="product-id" value="">
            <div class="form-group">
                <label for="product-name"><?php _e('Product Name', 'pet-shop-manager'); ?></label>
                <input type="text" id="product-name" required>
            </div>
            <div class="form-group">
                <label for="product-buying-price"><?php _e('Buying Price', 'pet-shop-manager'); ?></label>
                <input type="number" id="product-buying-price" step="0.01" required>
            </div>
            <button type="submit" class="button button-primary"><?php _e('Save Product', 'pet-shop-manager'); ?></button>
            <button type="button" class="button" id="close-modal"><?php _e('Cancel', 'pet-shop-manager'); ?></button>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function ($) {
    // Ensure only one event handler is attached
    $('#add-product').off('click').on('click', function () {
        $('#product-modal').stop(true, true).hide();
        $('#product-form')[0].reset();
        $('#product-id').val('');
        $('#product-modal').fadeIn();
    });

    $('#close-modal').off('click').on('click', function () {
        $('#product-modal').fadeOut();
    });

    // Close modal when clicking outside the modal content
    $(document).off('click.productModal').on('click.productModal', function (e) {
        if ($(e.target).closest('.modal-content').length === 0 && $(e.target).attr('id') !== 'add-product') {
            $('#product-modal').fadeOut();
        }
    });

    // Handle product form submission
    $('#product-form').off('submit').on('submit', function(e) {
        e.preventDefault();
        const productId = $('#product-id').val();
        const productName = $('#product-name').val();
        const buyingPrice = $('#product-buying-price').val();
        const data = {
            action: 'pet_shop_action',
            pet_action: productId ? 'edit_product' : 'add_product',
            nonce: pet_shop_ajax.nonce,
            name: productName,
            buying_price: buyingPrice
        };
        if (productId) {
            data.id = productId;
        }
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    $('#product-modal').fadeOut();
                    loadProducts();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        });
    });

    // Function to load products
    function loadProducts() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pet_shop_action',
                pet_action: 'get_products',
                nonce: pet_shop_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    const products = response.data;
                    let html = '';
                    if (products.length === 0) {
                        html = '<tr><td colspan="3"><?php _e('No products found.', 'pet-shop-manager'); ?></td></tr>';
                    } else {
                        products.forEach(function(product) {
                            html += `
                                <tr>
                                    <td>${product.name}</td>
                                    <td>৳${parseFloat(product.buying_price).toFixed(2)}</td>
                                    <td>
                                        <button class="button edit-product" data-id="${product.id}"><?php _e('Edit', 'pet-shop-manager'); ?></button>
                                        <button class="button button-danger delete-product" data-id="${product.id}"><?php _e('Delete', 'pet-shop-manager'); ?></button>
                                    </td>
                                </tr>
                            `;
                        });
                    }
                    // Always replace, never append
                    $('#product-list').html(html);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading products:', error);
            }
        });
    }

    // Edit product
    $(document).off('click.editProduct').on('click.editProduct', '.edit-product', function() {
        const productId = $(this).data('id');
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pet_shop_action',
                pet_action: 'get_product',
                nonce: pet_shop_ajax.nonce,
                id: productId
            },
            success: function(response) {
                if (response.success) {
                    const product = response.data;
                    $('#product-modal').stop(true, true).hide();
                    $('#product-form')[0].reset();
                    $('#product-id').val(product.id);
                    $('#product-name').val(product.name);
                    $('#product-buying-price').val(product.buying_price);
                    $('#product-modal').fadeIn();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error getting product:', error);
            }
        });
    });

    // Delete product
    $(document).off('click.deleteProduct').on('click.deleteProduct', '.delete-product', function() {
        const productId = $(this).data('id');
        if (confirm('<?php _e('Are you sure you want to delete this product?', 'pet-shop-manager'); ?>')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pet_shop_action',
                    pet_action: 'delete_product',
                    nonce: pet_shop_ajax.nonce,
                    id: productId
                },
                success: function(response) {
                    if (response.success) {
                        loadProducts();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error deleting product:', error);
                }
            });
        }
    });

    // Load products on page load
    loadProducts();
});
</script>


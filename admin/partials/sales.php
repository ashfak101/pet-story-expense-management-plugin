<div class="wrap pet-shop-manager">
    <h1><?php esc_html_e('Sales Tracking', 'pet-shop-manager'); ?></h1>
    <div id="sales-tracking">
        <button id="new-sale-btn" class="button button-primary"><?php esc_html_e('Log New Sale', 'pet-shop-manager'); ?></button>

        <h2><?php esc_html_e('Recent Sales', 'pet-shop-manager'); ?></h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php esc_html_e('Date', 'pet-shop-manager'); ?></th>
                    <th><?php esc_html_e('Items', 'pet-shop-manager'); ?></th>
                    <th><?php esc_html_e('Amount', 'pet-shop-manager'); ?></th>
                    <th><?php esc_html_e('Profit', 'pet-shop-manager'); ?></th>
                    <th><?php esc_html_e('Actions', 'pet-shop-manager'); ?></th>
                </tr>
            </thead>
            <tbody id="sales-list">
                <!-- Sales data will be populated here via AJAX -->
            </tbody>
        </table>
    </div>
</div>

<!-- Add Sales Modal -->
<div id="sale-modal" style="display:none;" class="modal">
    <div class="modal-content">
        <h2><?php esc_html_e('Log Sale', 'pet-shop-manager'); ?></h2>
        <form id="sale-form">
            <div class="form-group">
                <label for="sale-date"><?php esc_html_e('Date', 'pet-shop-manager'); ?></label>
                <input type="date" id="sale-date" required value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="form-group">
                <label><?php esc_html_e('Products Sold', 'pet-shop-manager'); ?></label>
                <div id="sale-items-container">
                    <div class="sale-item">
                        <select class="product-select" required>
                            <option value=""><?php esc_html_e('Select Product', 'pet-shop-manager'); ?></option>
                            <!-- Products will be loaded via JS -->
                        </select>
                        <input type="number" class="product-quantity" min="1" value="1" required placeholder="<?php esc_html_e('Qty', 'pet-shop-manager'); ?>">
                        <button type="button" class="button remove-item" style="display:none;">&times;</button>
                    </div>
                </div>
                <button type="button" class="button" id="add-item-btn"><?php esc_html_e('Add Another Product', 'pet-shop-manager'); ?></button>
            </div>
            <div class="form-group">
                <label for="selling-price"><?php esc_html_e('Selling Price', 'pet-shop-manager'); ?></label>
                <input type="number" step="0.01" id="selling-price" required>
            </div>
            <div class="form-group">
                <label for="is-delivery"><?php esc_html_e('Delivery?', 'pet-shop-manager'); ?></label>
                <input type="checkbox" id="is-delivery">
            </div>
            <div class="form-group">
                <label for="is-bkash"><?php esc_html_e('Paid via bKash?', 'pet-shop-manager'); ?></label>
                <input type="checkbox" id="is-bkash">
            </div>
            <input type="hidden" id="sale-id">
            <button type="submit" class="button button-primary"><?php esc_html_e('Save Sale', 'pet-shop-manager'); ?></button>
            <button type="button" class="button" id="close-sale-modal"><?php esc_html_e('Cancel', 'pet-shop-manager'); ?></button>
        </form>
    </div>
</div>

<script>
    jQuery(document).ready(function ($) {
        // Load products for the dropdown
        function loadProductsForDropdown() {
            // Make an AJAX request to get all products
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
                        // Populate the product dropdown
                        let options = '<option value=""><?php esc_html_e('Select Product', 'pet-shop-manager'); ?></option>';
                        products.forEach(function(product) {
                            options += `<option value="${product.id}" data-price="${product.buying_price}">${product.name}</option>`;
                        });
                        $('.product-select').html(options);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error loading products:", error);
                }
            });
        }

        // Open the sales modal
        $('#new-sale-btn').on('click', function() {
            $('#sale-modal').fadeIn();
            $('#sale-form')[0].reset();
            $('#sale-id').val('');
            $('#sale-date').val(new Date().toISOString().split('T')[0]);
            

            // Load products for the dropdown
            loadProductsForDropdown();

            // Reset sale items to just one
            $('#sale-items-container').html(`
                <div class="sale-item">
                    <select class="product-select" required>
                        <option value=""><?php esc_html_e('Select Product', 'pet-shop-manager'); ?></option>
                    </select>
                    <input type="number" class="product-quantity" min="1" value="1" required placeholder="<?php esc_html_e('Qty', 'pet-shop-manager'); ?>">
                    <button type="button" class="button remove-item" style="display:none;">&times;</button>
                </div>
            `);
        });

        // Close the sales modal
        $('#close-sale-modal').on('click', function() {
            $('#sale-modal').fadeOut();
        });

        // Close modal when clicking outside
        $(document).on('click', function(e) {
            if ($(e.target).closest('.modal-content').length === 0 && $(e.target).attr('id') !== 'new-sale-btn') {
                $('#sale-modal').fadeOut();
            }
        });

        // Add another product line
        $('#add-item-btn').on('click', function() {
            const newItem = `
                <div class="sale-item">
                    <select class="product-select" required>
                        <option value=""><?php esc_html_e('Select Product', 'pet-shop-manager'); ?></option>
                        <!-- Options will be populated dynamically -->
                    </select>
                    <input type="number" class="product-quantity" min="1" value="1" required placeholder="<?php esc_html_e('Qty', 'pet-shop-manager'); ?>">
                    <button type="button" class="button remove-item">&times;</button>
                </div>
            `;
            $('#sale-items-container').append(newItem);

            // Load products for the new dropdown
            loadProductsForDropdown();
        });

        // Remove product line
        $(document).on('click', '.remove-item', function() {
            $(this).closest('.sale-item').remove();
        });

        // Handle sale form submission
        $('#sale-form').on('submit', function(e) {
            e.preventDefault();

            // Collect items data
            const items = [];
            $('.sale-item').each(function() {
                const productId = $(this).find('.product-select').val();
                const quantity = $(this).find('.product-quantity').val();

                if (productId && quantity) {
                    items.push({
                        productId: productId,
                        quantity: quantity
                    });
                }
            });

            // Collect form data
            const saleData = {
                sale_date: $('#sale-date').val(),
                selling_price: $('#selling-price').val(),
                is_delivery: $('#is-delivery').is(':checked') ? 1 : 0,
                is_bkash: $('#is-bkash').is(':checked') ? 1 : 0,
                items: JSON.stringify(items),
                sale_id: $('#sale-id').val()
            };

            // Make AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pet_shop_action',
                    pet_action: $('#sale-id').val() ? 'edit_sale' : 'add_sale',
                    nonce: pet_shop_ajax.nonce,
                    ...saleData
                },
                success: function(response) {
                    if (response.success) {
                        $('#sale-modal').fadeOut();
                        // Reload sales list
                        loadSales();
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error saving sale:", error);
                    alert('Error saving sale. Please try again.');
                }
            });
        });

        // Function to load sales
        function loadSales() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pet_shop_action',
                    pet_action: 'get_sales',
                    nonce: pet_shop_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const sales = response.data;
                        let salesHtml = '';

                        if (sales.length === 0) {
                            salesHtml = '<tr><td colspan="5"><?php esc_html_e('No sales recorded yet.', 'pet-shop-manager'); ?></td></tr>';
                        } else {
                            sales.forEach(function(sale) {
                                // Calculate total cost and profit
                                let totalCost = 0;
                                let itemsText = '';

                                sale.items.forEach(function(item) {
                                    totalCost += item.buying_price * item.quantity;
                                    itemsText += `${item.product_name} (${item.quantity}), `;
                                });

                                // Remove trailing comma and space
                                itemsText = itemsText.replace(/, $/, '');

                                const profit = sale.selling_price - totalCost;
                                const profitClass = profit >= 0 ? 'positive' : 'negative';

                                salesHtml += `
                                    <tr>
                                        <td>${new Date(sale.sale_date).toLocaleDateString()}</td>
                                        <td>${itemsText}</td>
                                        <td>৳${parseFloat(sale.selling_price).toFixed(2)}</td>
                                        <td class="${profitClass}">৳${parseFloat(profit).toFixed(2)}</td>
                                        <td>
                                            <button class="button edit-sale" data-id="${sale.id}">Edit</button>
                                            <button class="button button-danger delete-sale" data-id="${sale.id}">Delete</button>
                                        </td>
                                    </tr>
                                `;
                            });
                        }

                        $('#sales-list').html(salesHtml);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error loading sales:", error);
                }
            });
        }

        // Load sales when the page loads
        loadSales();

        // Handle edit sale button
        $(document).on('click', '.edit-sale', function() {
            const saleId = $(this).data('id');
            // Get sale details
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pet_shop_action',
                    pet_action: 'get_sale',
                    nonce: pet_shop_ajax.nonce,
                    id: saleId
                },
                success: function(response) {
                    if (response.success) {
                        const sale = response.data;
                        // Load products for dropdowns first
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'pet_shop_action',
                                pet_action: 'get_products',
                                nonce: pet_shop_ajax.nonce
                            },
                            success: function(prodResponse) {
                                if (prodResponse.success) {
                                    const products = prodResponse.data;
                                    let options = '<option value=""><?php esc_html_e('Select Product', 'pet-shop-manager'); ?></option>';
                                    products.forEach(function(product) {
                                        options += `<option value="${product.id}" data-price="${product.buying_price}">${product.name}</option>`;
                                    });
                                    // Populate sale items
                                    let itemsHtml = '';
                                    sale.items.forEach(function(item, idx) {
                                        itemsHtml += `<div class="sale-item">
                                            <select class="product-select" required>${options}</select>
                                            <input type="number" class="product-quantity" min="1" value="${item.quantity}" required placeholder="<?php esc_html_e('Qty', 'pet-shop-manager'); ?>">
                                            <button type="button" class="button remove-item" style="${sale.items.length > 1 ? '' : 'display:none;'}">&times;</button>
                                        </div>`;
                                    });
                                    $('#sale-items-container').html(itemsHtml);
                                    // Set selected products
                                    $('#sale-items-container .sale-item').each(function(i) {
                                        $(this).find('.product-select').val(sale.items[i].product_id);
                                    });
                                    // Fill other fields
                                    $('#sale-id').val(sale.id);
                                    $('#sale-date').val(sale.sale_date);
                                    $('#selling-price').val(sale.selling_price);
                                    $('#is-delivery').prop('checked', sale.is_delivery == 1);
                                    $('#is-bkash').prop('checked', sale.is_bkash == 1);
                                    // Open modal after everything is set
                                    $('#sale-modal').fadeIn();
                                }
                            }
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error loading sale details:", error);
                }
            });
        });

        // Handle delete sale button
        $(document).on('click', '.delete-sale', function() {
            const saleId = $(this).data('id');

            if (confirm('Are you sure you want to delete this sale?')) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'pet_shop_action',
                        pet_action: 'delete_sale',
                        nonce: pet_shop_ajax.nonce,
                        id: saleId
                    },
                    success: function(response) {
                        if (response.success) {
                            loadSales();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error deleting sale:", error);
                    }
                });
            }
        });
    });
</script>
<div class="wrap pet-shop-manager">
    <h1><?php esc_html_e('Expenses Management', 'pet-shop-manager'); ?></h1>
    <div id="expenses-table-container">
        <button class="button button-primary" id="add-expense-btn"><?php esc_html_e('Add New Expense', 'pet-shop-manager'); ?></button>
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php esc_html_e('Date', 'pet-shop-manager'); ?></th>
                    <th><?php esc_html_e('Description', 'pet-shop-manager'); ?></th>
                    <th><?php esc_html_e('Amount', 'pet-shop-manager'); ?></th>
                    <th><?php esc_html_e('Actions', 'pet-shop-manager'); ?></th>
                </tr>
            </thead>
            <tbody id="expenses-table-body">
                <tr>
                    <td colspan="4"><?php esc_html_e('No expenses recorded yet.', 'pet-shop-manager'); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div id="expense-modal" style="display:none;" class="modal">
        <div class="modal-content">
            <h2><?php esc_html_e('Add/Edit Expense', 'pet-shop-manager'); ?></h2>
            <form id="expense-form">
                <div class="form-group">
                    <label for="expense-date"><?php esc_html_e('Date', 'pet-shop-manager'); ?></label>
                    <input type="date" id="expense-date" required>
                </div>
            <div class="form-group">
                <label for="expense-description"><?php esc_html_e('Description', 'pet-shop-manager'); ?></label>
                <input type="text" id="expense-description" required>
            </div>
            <div class="form-group">
                <label for="expense-amount"><?php esc_html_e('Amount', 'pet-shop-manager'); ?></label>
                <input type="number" id="expense-amount" step="0.01" required>
            </div>
            <input type="hidden" id="expense-id">
            <button type="submit" class="button button-primary"><?php esc_html_e('Save Expense', 'pet-shop-manager'); ?></button>
            <button type="button" class="button" id="close-modal-btn"><?php esc_html_e('Cancel', 'pet-shop-manager'); ?></button>
        </form>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function ($) {
        // Open the expense modal for adding
        $('#add-expense-btn').on('click', function () {
            $('#expense-form')[0].reset();
            $('#expense-id').val('');
            $('#expense-date').val(new Date().toISOString().split('T')[0]);
            $('#expense-modal').fadeIn();
        });

        // Close the expense modal
        $('#close-modal-btn').on('click', function () {
            $('#expense-modal').fadeOut();
        });

        // Close modal when clicking outside the modal content
        $(document).on('click', function (e) {
            if ($(e.target).closest('.modal-content').length === 0 && $(e.target).attr('id') !== 'add-expense-btn') {
                $('#expense-modal').fadeOut();
            }
        });

        // Load expenses
        function loadExpenses() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pet_shop_action',
                    pet_action: 'get_expenses',
                    nonce: pet_shop_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const expenses = response.data;
                        console.log(expenses);
                        let html = '';
                        if (expenses.length === 0) {
                            html = '<tr><td colspan="4"><?php esc_html_e('No expenses recorded yet.', 'pet-shop-manager'); ?></td></tr>';
                        } else {
                            expenses.forEach(function(expense) {
                                html += `<tr>
                                    <td>${expense.expense_date}</td>
                                    <td>${expense.description}</td>
                                    <td>৳${parseFloat(expense.amount).toFixed(2)}</td>
                                    <td>
                                        <button class="button edit-expense" data-id="${expense.id}"><?php esc_html_e('Edit', 'pet-shop-manager'); ?></button>
                                        <button class="button button-danger delete-expense" data-id="${expense.id}"><?php esc_html_e('Delete', 'pet-shop-manager'); ?></button>
                                    </td>
                                </tr>`;
                            });
                        }
                        $('#expenses-table-body').html(html);
                    }
                }
            });
        }

        // Handle expense form submission
        $('#expense-form').on('submit', function (e) {
            e.preventDefault();
            const expenseId = $('#expense-id').val();
            const expenseDate = $('#expense-date').val();
            console.log('Expense Date:', expenseDate);
            const expenseDescription = $('#expense-description').val();
            const expenseAmount = $('#expense-amount').val();
            const data = {
                action: 'pet_shop_action',
                pet_action: expenseId ? 'edit_expense' : 'add_expense',
                nonce: pet_shop_ajax.nonce,
                expense_date: expenseDate,
                description: expenseDescription,
                amount: expenseAmount
            };
            if (expenseId) {
                data.id = expenseId;
            }
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        $('#expense-modal').fadeOut();
                        loadExpenses();
                    } else {
                        alert(response.data ? response.data : 'An error occurred. Please try again.');
                    }
                },
                error: function(xhr, status, error) {
                    alert('A server error occurred: ' + error);
                }
            });
        });

        // Edit expense
        $(document).on('click', '.edit-expense', function() {
            const expenseId = $(this).data('id');
            // Clear form before populating
            $('#expense-form')[0].reset();
            $('#expense-id').val('');
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pet_shop_action',
                    pet_action: 'get_expense',
                    nonce: pet_shop_ajax.nonce,
                    id: expenseId
                },
                success: function(response) {
                    if (response.success) {
                        const expense = response.data;
                        $('#expense-id').val(expense.id);
                        $('#expense-date').val(expense.date);
                        $('#expense-description').val(expense.description);
                        $('#expense-amount').val(expense.amount);
                        $('#expense-modal').fadeIn();
                    } else {
                        alert(response.data ? response.data : 'Could not load expense.');
                    }
                },
                error: function(xhr, status, error) {
                    alert('A server error occurred: ' + error);
                }
            });
        });

        // Delete expense
        $(document).on('click', '.delete-expense', function() {
            const expenseId = $(this).data('id');
            if (confirm('<?php esc_html_e('Are you sure you want to delete this expense?', 'pet-shop-manager'); ?>')) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'pet_shop_action',
                        pet_action: 'delete_expense',
                        nonce: pet_shop_ajax.nonce,
                        id: expenseId
                    },
                    success: function(response) {
                        if (response.success) {
                            loadExpenses();
                        }
                    }
                });
            }
        });

        // Initial load
        loadExpenses();
    });
</script>
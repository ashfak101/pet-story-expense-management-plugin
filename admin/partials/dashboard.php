<div class="wrap pet-shop-manager">
    <h1><?php esc_html_e('Pet Shop Dashboard', 'pet-shop-manager'); ?></h1>

    <div class="card">
        <div class="date-filters">
            <button class="button btn-outline" data-range="today"><?php _e('Today', 'pet-shop-manager'); ?></button>
            <button class="button btn-outline" data-range="week"><?php _e('This Week', 'pet-shop-manager'); ?></button>
            <button class="button btn-outline" data-range="month"><?php _e('This Month', 'pet-shop-manager'); ?></button>
            <input type="date" id="date-from">
            <input type="date" id="date-to">
        </div>

        <div class="stats-grid" id="dashboard-stats">
            <div class="stat-card">
                <h3><?php esc_html_e('Total Products', 'pet-shop-manager'); ?></h3>
                <p id="total-products">0</p>
            </div>
            <div class="stat-card">
                <h3><?php esc_html_e('Total Sales', 'pet-shop-manager'); ?></h3>
                <p id="total-sales">৳0.00</p>
            </div>
            <div class="stat-card">
                <h3><?php esc_html_e('Total Expenses', 'pet-shop-manager'); ?></h3>
                <p id="total-expenses">৳0.00</p>
            </div>
            <div class="stat-card">
                <h3><?php esc_html_e('Net Profit', 'pet-shop-manager'); ?></h3>
                <p id="net-profit" class="profit">৳0.00</p>
            </div>
        </div>
    </div>

    <div class="card">
        <h2><?php esc_html_e('Recent Sales', 'pet-shop-manager'); ?></h2>
        <div class="table-wrapper">
            <table class="widefat" id="dashboard-sales-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Date', 'pet-shop-manager'); ?></th>
                        <th><?php esc_html_e('Time', 'pet-shop-manager'); ?></th>
                        <th><?php esc_html_e('Items', 'pet-shop-manager'); ?></th>
                        <th><?php esc_html_e('Profit', 'pet-shop-manager'); ?></th>
                        <th><?php esc_html_e('Total', 'pet-shop-manager'); ?></th>
                        <th><?php esc_html_e('Actions', 'pet-shop-manager'); ?></th>
                    </tr>
                </thead>
                <tbody id="recent-sales-body">
                    <tr>
                        <td colspan="6"><?php esc_html_e('No sales recorded yet.', 'pet-shop-manager'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function ($) {
    function loadDashboardStats(range = '', from = '', to = '') {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pet_shop_action',
                pet_action: 'get_dashboard_stats',
                nonce: pet_shop_ajax.nonce,
                range: range,
                from: from,
                to: to
            },
            success: function(response) {
                if (response.success) {
                    const stats = response.data;
                    $('#total-products').text(stats.total_products);
                    $('#total-sales').text('৳' + parseFloat(stats.total_sales).toFixed(2));
                    $('#total-expenses').text('৳' + parseFloat(stats.total_expenses).toFixed(2));
                    $('#net-profit').text('৳' + parseFloat(stats.net_profit).toFixed(2));
                }
            }
        });
    }

    function loadRecentSales(range = '', from = '', to = '') {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pet_shop_action',
                pet_action: 'get_recent_sales',
                nonce: pet_shop_ajax.nonce,
                range: range,
                from: from,
                to: to
            },
            success: function(response) {
                if (response.success) {
                    const sales = response.data;
                    let html = '';
                    if (sales.length === 0) {
                        html = '<tr><td colspan="6"><?php esc_html_e('No sales recorded yet.', 'pet-shop-manager'); ?></td></tr>';
                    } else {
                        sales.forEach(function(sale) {
                            html += `<tr>
                                <td>${sale.date}</td>
                                <td>${sale.time}</td>
                                <td>${sale.items}</td>
                                <td>৳${parseFloat(sale.profit).toFixed(2)}</td>
                                <td>৳${parseFloat(sale.total).toFixed(2)}</td>
                                <td><button class="button view-sale" data-id="${sale.id}"><?php esc_html_e('View', 'pet-shop-manager'); ?></button></td>
                            </tr>`;
                        });
                    }
                    $('#recent-sales-body').html(html);
                }
            }
        });
    }

    // Date filter buttons
    $('.date-filters button').on('click', function() {
        const range = $(this).data('range');
        loadDashboardStats(range);
        loadRecentSales(range);
    });
    $('#date-from, #date-to').on('change', function() {
        const from = $('#date-from').val();
        const to = $('#date-to').val();
        loadDashboardStats('', from, to);
        loadRecentSales('', from, to);
    });

    // Initial load
    loadDashboardStats();
    loadRecentSales();
});
</script>
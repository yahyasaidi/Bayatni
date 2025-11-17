<aside class="sidebar">
    <div class="sidebar-header">
        <h2>Bayatni</h2>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li <?php echo basename($_SERVER['SCRIPT_NAME']) == 'index.php' ? 'class="active"' : ''; ?>>
                <a href="/development/admin/index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </li>
            <li <?php echo basename($_SERVER['SCRIPT_NAME']) == 'hotels.php' ? 'class="active"' : ''; ?>>
                <a href="/development/admin/app/controllers/hotels/hotels.php"><i class="fas fa-hotel"></i> Hotels</a>
            </li>
            <li <?php echo basename($_SERVER['SCRIPT_NAME']) == 'users.php' ? 'class="active"' : ''; ?>>
                <a href="/development/admin/app/controllers/users/users.php"><i class="fas fa-users"></i> Users</a>
            </li>
            <li <?php echo basename($_SERVER['SCRIPT_NAME']) == 'bookings.php' ? 'class="active"' : ''; ?>>
                <a href="/development/admin/app/controllers/bookings/bookings.php"><i class="fas fa-calendar-check"></i> Bookings</a>
            </li>
            <li <?php echo basename($_SERVER['SCRIPT_NAME']) == 'reviews.php' ? 'class="active"' : ''; ?>>
                <a href="/development/admin/app/controllers/reviews/reviews.php"><i class="fas fa-star"></i> Reviews</a>
            </li>
        </ul>
    </nav>
</aside>

<script>
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        document.documentElement.classList.add('sidebar-collapsed');
    }
</script>
<style>
    [x-cloak] { display: none !important; }
    
    /* Pre-load collapsed state to prevent FOUC */
    html.sidebar-collapsed aside {
        width: 5rem !important;
    }
    html.sidebar-collapsed aside .logo-container {
        width: 2.5rem !important;
        height: 2.5rem !important;
    }
    html.sidebar-collapsed aside .logo-img {
        width: 2.5rem !important;
        height: 2.5rem !important;
    }
    html.sidebar-collapsed aside .sidebar-text {
        display: none !important;
    }
    html.sidebar-collapsed aside .fa-chevron-left {
        display: none !important;
    }
    html.sidebar-collapsed aside .fa-chevron-right {
        display: block !important;
    }
    
    /* Disable transitions initially */
    .no-transition, .no-transition * {
        transition: none !important;
    }
    
    /* Sidebar Navigation Global Styles */
    /* Default expanded state: ensure margin is present immediately */
    aside nav a i, 
    aside nav button i,
    aside nav .nav-icon {
        margin-right: 0.75rem;
    }
    
    /* Collapsed state overrides */
    html.sidebar-collapsed aside nav a,
    html.sidebar-collapsed aside nav button,
    aside.sidebar-collapsed nav a,
    aside.sidebar-collapsed nav button {
        justify-content: center !important;
    }
    
    html.sidebar-collapsed aside nav a i,
    html.sidebar-collapsed aside nav button i,
    aside.sidebar-collapsed nav a i,
    aside.sidebar-collapsed nav button i,
    html.sidebar-collapsed aside nav .nav-icon,
    aside.sidebar-collapsed nav .nav-icon {
        margin-right: 0 !important;
    }
    
    /* Handle the nested div in dropdown buttons */
    html.sidebar-collapsed aside nav button div,
    aside.sidebar-collapsed nav button div {
        justify-content: center;
    }
</style>

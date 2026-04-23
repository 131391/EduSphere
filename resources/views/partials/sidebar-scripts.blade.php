<script>
    // ── Sidebar: apply collapsed state synchronously before first paint ──────
    (function () {
        var collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (collapsed) {
            document.documentElement.classList.add('sidebar-collapsed');
        }

        var currentPath = window.location.pathname;
        if (localStorage.getItem('favorited_' + currentPath) === 'true') {
            document.documentElement.classList.add('page-favorited');
        }
        if (localStorage.getItem('bookmarked_' + currentPath) === 'true') {
            document.documentElement.classList.add('page-bookmarked');
        }
    })();

    // Pre-load header action states to prevent icon blinking
    document.addEventListener('DOMContentLoaded', function() {
        var currentPath = window.location.pathname;

        var isFavorited = localStorage.getItem('favorited_' + currentPath) === 'true';
        var favoriteButton = document.querySelector('[title="Add to favorites"], [title="Add to Favorites"]');
        var favoriteIcon = favoriteButton ? favoriteButton.querySelector('i') : null;
        if (favoriteIcon && isFavorited) {
            favoriteIcon.classList.remove('far');
            favoriteIcon.classList.add('fas');
        }

        var isBookmarked = localStorage.getItem('bookmarked_' + currentPath) === 'true';
        var bookmarkButton = document.querySelector('[title="Bookmark this page"], [title="Saved Pages"]');
        var bookmarkIcon = bookmarkButton ? bookmarkButton.querySelector('i') : null;
        if (bookmarkIcon && isBookmarked) {
            bookmarkIcon.classList.remove('far');
            bookmarkIcon.classList.add('fas');
        }
    });
</script>
<style>
    [x-cloak] { display: none !important; }
    
    /* Default sidebar width before Alpine hydrates */
    aside {
        width: 16rem;
    }
    html.sidebar-collapsed aside {
        width: 5rem;
    }
    html.sidebar-collapsed aside .logo-container {
        width: 2.5rem !important;
        height: 2.5rem !important;
    }
    html.sidebar-collapsed aside .logo-img {
        width: 2.5rem !important;
        height: 2.5rem !important;
    }
    html.sidebar-collapsed aside .logo-container i {
        font-size: 1.125rem !important;
        line-height: 1 !important;
    }

    /* sidebar-text: visible when expanded, removed from layout when collapsed.
       Keeping collapsed labels only visually hidden leaves them in the flex flow,
       which shifts icons off-center and clips them in narrow sidebars. */
    aside .sidebar-text {
        visibility: visible;
        opacity: 1;
        transition: opacity 150ms ease, visibility 0s;
    }
    html.sidebar-collapsed aside .sidebar-text,
    aside.sidebar-collapsed .sidebar-text {
        display: none !important;
        visibility: hidden;
        opacity: 0;
    }
    /* School/receptionist layouts also hide many expanded-only labels with
       x-show instead of the shared .sidebar-text class. Hide those eagerly
       during collapsed first paint to avoid label/icon blinking on navigation. */
    html.sidebar-collapsed aside [x-show="!sidebarCollapsed"] {
        display: none !important;
    }

    /* Nav icon default margin-right: matches Alpine's :class="{ 'mr-3': !sidebarCollapsed }".
       Without this, text sits flush against the icon pre-hydration and jerks right ~12px
       once Alpine attaches mr-3. Collapsed state resets it to 0 below. */
    aside nav a i,
    aside nav button i {
        flex-shrink: 0;
        margin-right: 0.75rem;
    }
    html.sidebar-collapsed aside nav a i,
    html.sidebar-collapsed aside nav button i,
    aside.sidebar-collapsed nav a i,
    aside.sidebar-collapsed nav button i {
        margin-right: 0 !important;
    }

    /* Hide sidebar on mobile before Alpine hydrates */
    @media (max-width: 1023px) {
        aside:not(.mobile-open) {
            transform: translateX(-100%);
        }
    }

    /* Collapsed state — CSS handles pre-hydration appearance only.
       Width is controlled by Alpine :style after hydration. */
    html.sidebar-collapsed aside .sidebar-submenu {
        display: none !important;
    }
    html.sidebar-collapsed aside .sidebar-collapsed-only {
        display: block !important;
    }
    html.sidebar-collapsed aside .fa-chevron-left {
        display: none !important;
    }
    html.sidebar-collapsed aside .fa-chevron-right {
        display: block !important;
    }
    html.sidebar-collapsed aside nav a,
    html.sidebar-collapsed aside nav button {
        justify-content: center !important;
    }
    html.sidebar-collapsed aside nav a i,
    html.sidebar-collapsed aside nav button i {
        margin-right: 0 !important;
    }

    /* Disable ALL transitions until Alpine has fully hydrated.
       The no-transition class is removed by Alpine init() after a tick. */
    .no-transition, .no-transition * {
        transition: none !important;
    }

    /* Dark mode toggle SSR fallback: the fallback icon is rendered as
       `far fa-moon`. When dark mode is already active on refresh, Alpine
       later swaps it to `fas fa-sun`, causing a visible flash moon→sun.
       The inline `darkMode` script sets html.dark synchronously before
       first paint, so we override the fallback's FA glyph via CSS —
       showing the correct sun icon on initial render. */
    html.dark .ssr-icon-fallback.fa-moon::before {
        content: "\f185"; /* fa-sun codepoint */
        font-weight: 900; /* solid (fas) weight */
    }
    html.page-favorited .ssr-icon-fallback.fa-star::before {
        content: "\f005"; /* fa-star codepoint */
        font-weight: 900; /* solid (fas) weight */
    }
    html.page-bookmarked .ssr-icon-fallback.fa-bookmark::before {
        content: "\f02e"; /* fa-bookmark codepoint */
        font-weight: 900; /* solid (fas) weight */
    }
    /* Match the yellow tint Alpine applies post-hydration
       (:class="{ 'text-yellow-400': isDarkMode }") so the colour doesn't
       flicker gray→yellow on the dark-mode button. */
    html.dark [title="Toggle dark mode"],
    html.dark [title="Toggle Dark Mode"] {
        color: #fbbf24; /* text-yellow-400 */
    }
    html.page-favorited [title="Add to favorites"],
    html.page-favorited [title="Add to Favorites"] {
        color: #eab308; /* yellow-500 */
    }
    html.page-bookmarked [title="Bookmark this page"],
    html.page-bookmarked [title="Saved Pages"] {
        color: #3b82f6; /* blue-500 */
    }
</style>

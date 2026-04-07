<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Class Namespace
    |--------------------------------------------------------------------------
    |
    | This value sets the root namespace that Livewire component classes are
    | resolved from. For example, if you set this to 'App\\Livewire', and you
    | create a component at app/Livewire/Pages/HomePage.php, the component
    | class would be App\Livewire\Pages\HomePage.
    |
    */
    'class_namespace' => 'App\\Livewire',

    /*
    |--------------------------------------------------------------------------
    | View Path
    |--------------------------------------------------------------------------
    |
    | This value sets the path where Livewire component views are stored.
    | For example, if you set this to 'resources/views/livewire', views for
    | components would be stored at 'resources/views/livewire/*.blade.php'.
    |
    */
    'view_path' => 'resources/views/livewire',

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | This configuration allows you to define the default layout that will be
    | used when rendering Livewire components. This layout can be overridden
    | per component with the #[Layout(...)] attribute on component classes.
    |
    */
    'layout' => 'layouts.app',

    /*
    |--------------------------------------------------------------------------
    | Lazy Placeholder
    |--------------------------------------------------------------------------
    |
    | This configuration allows you to define placeholder components that can
    | be lazy-loaded. For example, you can define a 'skeleton' placeholder
    | that is rendered while a lazy component is loading.
    |
    */
    'lazy_placeholder' => null,

    /*
    |--------------------------------------------------------------------------
    | Temporary File Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Livewire temporarily stores file uploads before they are handled.
    |
    */
    'temporary_file_upload' => [
        'disk' => null, // will use the default disk
        'rules' => 'file|max:12288', // 12 MB
        'directory' => 'livewire-tmp',
    ],

    /*
    |--------------------------------------------------------------------------
    | Render Hooks
    |--------------------------------------------------------------------------
    |
    | This option allows you to define custom render hooks that Livewire will
    | call at specific points during the render lifecycle. This is useful for
    | injecting custom HTML or logic into components.
    |
    */
    'render_hooks' => [
        // 'html.head' => HtmlHeadHook::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Morphdom
    |--------------------------------------------------------------------------
    |
    | Livewire uses morphdom to efficiently update the DOM when component
    | state changes. These configuration options allow you to customize the
    | morphdom behavior.
    |
    */
    'morphdom' => [
        'enabled' => true,
        'sibling_nodes' => true,
        'child_text_nodes' => true,
        'form_elements' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Script Assets
    |--------------------------------------------------------------------------
    |
    | This configuration allows you to customize which Livewire frontend
    | library is loaded. By default, Livewire will use its bundled library.
    |
    */
    'script_assets' => [
        'source' => 'cdn', // 'cdn' or 'inline'
    ],

    /*
    |--------------------------------------------------------------------------
    | Alpine.js (Recommended)
    |--------------------------------------------------------------------------
    |
    | It is highly recommended to use Alpine.js with Livewire for the best
    | user experience. Here you can configure Alpine.js settings.
    |
    */
    'alpine' => [
        'enabled' => true,
    ],
];

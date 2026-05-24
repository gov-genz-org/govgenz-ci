<?php

declare(strict_types=1);

if (! function_exists('admin_staff_is_editor_only')) {
    /** Modérateur / rédacteur : pas admin technique. */
    function admin_staff_is_editor_only(): bool
    {
        return session()->get('staff_role') === 'editor';
    }
}


<?php

if (! function_exists('queue_connection')) {
    function queue_connection() {
        return config('queue.connections.' . config('queue.default') . '.connection');
    }
}

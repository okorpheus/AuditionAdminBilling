<?php

if (! function_exists('formatMoney')) {
    function formatMoney(int|float $dollars): string
    {
        return '$'.number_format($dollars, 2);
    }
}
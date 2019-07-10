<?php
function format_money($money)
{
    return number_format($money/100, 2); 
}
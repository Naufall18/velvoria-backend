<?php

namespace App\Exceptions;

use Exception;

// Domain error while building order(s) from a cart (empty cart, missing
// product, insufficient stock). Carries a user-facing message; the controller
// translates it into the API's 422 error shape.
class OrderCreationException extends Exception
{
}

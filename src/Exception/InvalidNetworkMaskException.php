<?php
namespace ShoppingFeed\Ip\Exception;

class InvalidNetworkMaskException extends Exception
{
    const UNKNOWN              = 0;
    const INVALID_FORMAT       = 1;
    const NO_NETWORK_ADDRESS   = 2;
    const NO_BROADCAST_ADDRESS = 3;
    const INVALID_VALUE        = 4;
    const NO_CIDR_VALUE        = 5;
}

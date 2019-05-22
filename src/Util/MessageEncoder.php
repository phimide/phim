<?php
namespace Util;

class MessageEncoder
{
    public static function encode($dataArray) {
        return msgpack_pack($dataArray);
    }

    public static function decode($dataString) {
        return msgpack_unpack($dataString);
    }
}

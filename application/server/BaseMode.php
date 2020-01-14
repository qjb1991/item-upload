<?php
/**
 * Created by item-upload
 * User: bibo
 * Date: 2020-01-13
 * Time: 15:49
 */

namespace app\server;


abstract class BaseMode
{
    abstract function index($decode, $param);
}
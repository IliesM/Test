<?php
/**
 * Created by IntelliJ IDEA.
 * User: ilies
 * Date: 14/04/18
 * Time: 11:24
 */

namespace Logger;

interface ILogger
{
    public function info($message, $extraInfo = []);
    public function warn($message, $extraInfo = []);
    public function error($message, $extraInfo = []);
    public function debug($message, $extraInfo = []);
}
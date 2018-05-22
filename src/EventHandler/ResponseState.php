<?php
/**
 * Created by IntelliJ IDEA.
 * User: ilies
 * Date: 15/04/18
 * Time: 21:24
 */

namespace EventHandler;


class ResponseState
{
    const Success = 1;
    const Running = 2;
    const Failure = -1;
    const Logged = 3;
    const LoggedOut = 4;
    const Done = 6;
    const Ready = 7;
    const NotReady = 8;
}
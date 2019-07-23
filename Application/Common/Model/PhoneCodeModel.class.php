<?php

namespace Common\Model;

use Think\Model;

class PhoneCodeModel extends Model
{
    const STATUS_NO_LOGIN = 0;
    const STATUS_LOGIN = 1;
    const STATUS_BAN = 2;
}

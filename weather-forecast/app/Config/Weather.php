<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Weather extends BaseConfig
{
    public string $apiBaseUrl = 'https://api.open-meteo.com/v1/forecast';
}

<?php

namespace Verifarma\SerialCodesGenerator\Enums;

enum SerialRegulationProfile: string
{
    case DEFAULT = 'default';
    case EMVS = 'emvs';
    case US_DSCSA = 'us_dscsa';
}

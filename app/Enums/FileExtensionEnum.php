<?php

namespace App\Enums;

enum FileExtensionEnum: string
{
    case JPG = 'jpg';
    case JPEG = 'jpeg';
    case PNG = 'png';
    case PDF = 'pdf';
    case DOCX = 'docx';
    case DOC = 'doc';
    case ODT = 'odt';
}

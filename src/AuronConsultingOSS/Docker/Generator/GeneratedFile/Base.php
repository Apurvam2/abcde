<?php
/**
 * Copyright 2016 Luis Alberto Pabon Flores
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace AuronConsultingOSS\Docker\Generator\GeneratedFile;

use AuronConsultingOSS\Docker\Interfaces\GeneratedFileInterface;

/**
 * Base class for all generated files.
 *
 * @package AuronConsultingOSS\Docker\Generator\GeneratedFile
 * @author  Luis A. Pabon Flores
 */
abstract class Base implements GeneratedFileInterface
{
    /**
     * @var string
     */
    protected $contents;

    /**
     * You MUST provide the file contents on the constructor.
     *
     * @param string $contents
     */
    public function __construct(string $contents)
    {
        $this->contents = $contents;
    }

    /**
     * @return string
     */
    public function getContents() : string
    {
        return $this->contents;
    }
}

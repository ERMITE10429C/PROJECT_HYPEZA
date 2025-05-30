<?php
/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

namespace Google\Service\CloudAsset;

class GoogleIdentityAccesscontextmanagerV1EgressSource extends \Google\Model
{
  /**
   * @var string
   */
  public $accessLevel;
  /**
   * @var string
   */
  public $resource;

  /**
   * @param string
   */
  public function setAccessLevel($accessLevel)
  {
    $this->accessLevel = $accessLevel;
  }
  /**
   * @return string
   */
  public function getAccessLevel()
  {
    return $this->accessLevel;
  }
  /**
   * @param string
   */
  public function setResource($resource)
  {
    $this->resource = $resource;
  }
  /**
   * @return string
   */
  public function getResource()
  {
    return $this->resource;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleIdentityAccesscontextmanagerV1EgressSource::class, 'Google_Service_CloudAsset_GoogleIdentityAccesscontextmanagerV1EgressSource');

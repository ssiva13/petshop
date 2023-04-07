<?php
/**
 * Date 06/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Repositories\Post;

use App\Repositories\ModelInterface;

interface PostInterface extends ModelInterface
{
    public function getPaginated(array $data = []);
}

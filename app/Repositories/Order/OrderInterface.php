<?php
/**
 * Date 06/04/2023
 *
 * @author   Simon Siva <simonsiva13@gmail.com>
 */

namespace App\Repositories\Order;

use App\Repositories\ModelInterface;

interface OrderInterface extends ModelInterface
{
    public function getPaginated(array $data = []);
}

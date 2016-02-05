<?php echo "<?php\n"; ?>

namespace App\Db\Admin;

use App\Db\BaseDbObject;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use PeskyCMF\Db\Traits\Authenticatable;
use PeskyCMF\Db\Traits\UserDataSavingHelper;

/**
 * @package App\DbObject
 *
 * @property-read int       $id
 * @property-read string    $email
 * @property-read string    $password
 * @property-read string    $name
 * @property-read string    $created_at
 * @property-read string    $created_at_date
 * @property-read string    $created_at_time
 * @property-read string    $created_at_ts
 * @property-read string    $updated_at
 * @property-read string    $updated_at_date
 * @property-read string    $updated_at_time
 * @property-read string    $updated_at_ts
 * @property-read string    $remember_token
 * @property-read bool      $is_superadmin
 * @property-read bool      $is_active
 * @property-read int|null  $parent_id
 * @property-read string    $language
 * @property-read string    $role
 * @property-read string    $ip
 *
 * @method $this    setId($value)
 * @method $this    setEmail($value)
 * @method $this    setPassword($value)
 * @method $this    setName($value)
 * @method $this    setRememberToken($value)
 * @method $this    setIsSuperadmin($value)
 * @method $this    setIsActive($value)
 * @method $this    setParentId($value)
 * @method $this    setLanguage($value)
 * @method $this    setIp($value)
 * @method $this    setRole($value)
 *
 */
class Admin extends BaseDbObject implements AuthenticatableContract {

    use Authenticatable,
        UserDataSavingHelper;

}
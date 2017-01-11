<?php echo "<?php\n"; ?>

namespace App\<?php echo $dbClassesAppSubfolder ?>\<?php echo $baseClassNamePlural; ?>;

use PeskyCMF\Db\CmfDbRecord;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use PeskyCMF\Db\Traits\Authenticatable;

/**
 * @property-read int       $id
 * @property-read string    $email
 * @property-read string    $password
 * @property-read string    $name
 * @property-read string    $created_at
 * @property-read string    $created_at_as_date
 * @property-read string    $created_at_as_time
 * @property-read string    $created_at_as_unix_ts
 * @property-read string    $updated_at
 * @property-read string    $updated_at_as_date
 * @property-read string    $updated_at_as_time
 * @property-read string    $updated_at_as_unix_ts
 * @property-read string    $remember_token
 * @property-read bool      $is_superadmin
 * @property-read bool      $is_active
 * @property-read int|null  $parent_id
 * @property-read string    $language
 * @property-read string    $role
 * @property-read string    $ip
 *
 * @property-read <?php echo $baseClassNameSingular; ?>     $Parent<?php echo $baseClassNameSingular; ?>
 *
 * @method $this    setId($value, $isFromDb = false)
 * @method $this    setEmail($value, $isFromDb = false)
 * @method $this    setPassword($value, $isFromDb = false)
 * @method $this    setName($value, $isFromDb = false)
 * @method $this    setRememberToken($value, $isFromDb = false)
 * @method $this    setIsSuperadmin($value, $isFromDb = false)
 * @method $this    setIsActive($value, $isFromDb = false)
 * @method $this    setParentId($value, $isFromDb = false)
 * @method $this    setLanguage($value, $isFromDb = false)
 * @method $this    setIp($value, $isFromDb = false)
 * @method $this    setRole($value, $isFromDb = false)
 *
 */
class <?php echo $baseClassNameSingular; ?> extends CmfDbRecord implements AuthenticatableContract {

    use Authenticatable;

    /**
     * @return <?php echo $baseClassNamePlural; ?>Table
     */
    static public function getTable() {
        return <?php echo $baseClassNamePlural; ?>Table::getInstance();
    }

}
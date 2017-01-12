<?php echo "<?php\n"; ?>

namespace App\<?php echo $dbClassesAppSubfolder ?>\<?php echo $baseClassNamePlural; ?>;

use <?php echo $parentFullClassNameForRecord ?>;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use PeskyCMF\Db\Traits\Authenticatable;

/**
 * @property-read string      $id
 * @property-read null|string $parent_id
 * @property-read string      $name
 * @property-read string      $email
 * @property-read string      $password
 * @property-read string      $ip
 * @property-read string      $is_superadmin
 * @property-read string      $is_active
 * @property-read string      $role
 * @property-read string      $language
 * @property-read string      $created_at
 * @property-read string      $created_at_as_date
 * @property-read string      $created_at_as_time
 * @property-read int         $created_at_as_unix_ts
 * @property-read string      $updated_at
 * @property-read string      $updated_at_as_date
 * @property-read string      $updated_at_as_time
 * @property-read int         $updated_at_as_unix_ts
 * @property-read null|string $timezone
 * @property-read null|string $remember_token
 *
 * @property-read <?php echo $baseClassNameSingular; ?>       $Parent<?php echo $baseClassNameSingular . "\n"; ?>
 *
 * @method $this    setId($value, $isFromDb = false)
 * @method $this    setParentId($value, $isFromDb = false)
 * @method $this    setName($value, $isFromDb = false)
 * @method $this    setEmail($value, $isFromDb = false)
 * @method $this    setPassword($value, $isFromDb = false)
 * @method $this    setIp($value, $isFromDb = false)
 * @method $this    setIsSuperadmin($value, $isFromDb = false)
 * @method $this    setIsActive($value, $isFromDb = false)
 * @method $this    setRole($value, $isFromDb = false)
 * @method $this    setLanguage($value, $isFromDb = false)
 * @method $this    setTimezone($value, $isFromDb = false)
 * @method $this    setRememberToken($value, $isFromDb = false)
 */
class <?php echo $baseClassNameSingular; ?> extends <?php echo $parentClassNameForRecord ?> implements AuthenticatableContract {

    use Authenticatable;

    /**
     * @return <?php echo $baseClassNamePlural; ?>Table
     */
    static public function getTable() {
        return <?php echo $baseClassNamePlural; ?>Table::getInstance();
    }

}
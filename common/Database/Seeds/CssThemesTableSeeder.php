<?php namespace Common\Database\Seeds;

use App\User;
use Common\Admin\Appearance\Themes\CssTheme;
use Common\Auth\Permissions\Permission;
use Common\Settings\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CssThemesTableSeeder extends Seeder
{
    /**
     * @var Setting
     */
    private $theme;

    /**
     * @var User
     */
    private $user;

    /**
     * @param CssTheme $theme
     * @param User $user
     */
    public function __construct(CssTheme $theme, User $user)
    {
        $this->theme = $theme;
        $this->user = $user;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dark = config('common.themes.dark');
        $light = config('common.themes.light');

        $admin = $this->user->whereHas('permissions', function(Builder $builder) {
            $builder->where('name', 'admin');
        })->first();

        if ($this->theme->where('default_dark', true)->count() === 0) {
            $this->theme->create([
                'name' => 'Dark',
                'is_dark' => true,
                'default_dark' => true,
                'colors' => $dark,
                'user_id' => $admin->id,
            ]);
        }

        if ($this->theme->where('default_light', true)->count() === 0) {
            $this->theme->create([
                'name' => 'Light',
                'default_light' => true,
                'user_id' => $admin->id,
                'colors' => $light,
            ]);
        }
    }
}

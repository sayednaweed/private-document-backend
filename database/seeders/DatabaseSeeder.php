<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Contact;
use App\Models\Country;
use App\Models\Department;
use App\Models\District;
use App\Models\Email;
use App\Models\Language;
use App\Models\ModelJob;
use App\Models\Permission;
use App\Models\Province;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\Translate;
use App\Models\User;
use App\Models\UserPermission;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->languages();
        $email =  Email::factory()->create([
            "value" => "super@admin.com"
        ]);
        Role::factory()->create([
            "id" => RoleEnum::super,
            "name" => "super"
        ]);
        Role::factory()->create([
            "id" => RoleEnum::admin,
            "name" => "admin"
        ]);
     
      
        $contact =  Contact::factory()->create([
            "value" => "+93785764809"
        ]);
        $job =  ModelJob::factory()->create([
            "name" => "Administrator",
        ]);
        $this->Translate("مدیر", "fa", $job->id, ModelJob::class);

        $department =  Department::factory()->create([
            "name" => "Information Technology",
        ]);
        $this->Translate("تکنالوژی معلوماتی", "fa", $department->id, Department::class);
        User::factory()->create([
            'full_name' => 'Sayed Naweed Sayedy',
            'username' => 'super@admin.com',
            'email_id' =>  $email->id,
            'password' =>  Hash::make("123123123"),
            'status' =>  true,
            'grant_permission' =>  true,
            'role_id' =>  RoleEnum::super,
            'contact_id' =>  $contact->id,
            'job_id' =>  $job->id,
            'department_id' =>  $department->id,
        ]);
        // Icons
        $dashboard = 'public/icons/home.svg';
        $users = 'public/icons/users-group.svg';
        $chart = 'public/icons/chart.svg';
        $settings = 'public/icons/settings.svg';
        $logs = 'public/icons/logs.svg';
        Permission::factory()->create([
            "name" => "dashboard",
            "icon" => $dashboard,
            "priority" => 1
        ]);
        Permission::factory()->create([
            "name" => "users",
            "icon" => $users,
            "priority" => 2
        ]);
        Permission::factory()->create([
            "name" => "settings",
            "icon" => $settings,
            "priority" => 4
        ]);
        Permission::factory()->create([
            "name" => "reports",
            "icon" => $chart,
            "priority" => 3
        ]);
        Permission::factory()->create([
            "name" => "logs",
            "icon" => $logs,
            "priority" => 5
        ]);
        UserPermission::factory()->create([
            "view" => true,
            "edit" => true,
            "delete" => true,
            "add" => true,
            "user_id" => 1,
            "permission" => "dashboard"
        ]);
        UserPermission::factory()->create([
            "view" => true,
            "edit" => true,
            "delete" => true,
            "add" => true,
            "user_id" => 1,
            "permission" => "users"
        ]);
        UserPermission::factory()->create([
            "view" => true,
            "edit" => true,
            "delete" => true,
            "add" => true,
            "user_id" => 1,
            "permission" => "settings"
        ]);
        UserPermission::factory()->create([
            "view" => true,
            "edit" => true,
            "delete" => true,
            "add" => true,
            "user_id" => 1,
            "permission" => "reports"
        ]);
        UserPermission::factory()->create([
            "view" => true,
            "edit" => true,
            "delete" => true,
            "add" => true,
            "user_id" => 1,
            "permission" => "logs"
        ]);

        // 
        RolePermission::factory()->create([
            "view" => true,
            "edit" => true,
            "delete" => true,
            "add" => true,
            "role" => RoleEnum::super,
            "permission" => "dashboard"
        ]);
        RolePermission::factory()->create([
            "view" => true,
            "edit" => true,
            "delete" => true,
            "add" => true,
            "role" => RoleEnum::super,
            "permission" => "users"
        ]);
        RolePermission::factory()->create([
            "view" => true,
            "edit" => true,
            "delete" => true,
            "add" => true,
            "role" => RoleEnum::super,
            "permission" => "settings"
        ]);
        RolePermission::factory()->create([
            "view" => true,
            "edit" => true,
            "delete" => true,
            "add" => true,
            "role" => RoleEnum::super,
            "permission" => "reports"
        ]);
        RolePermission::factory()->create([
            "view" => true,
            "edit" => true,
            "delete" => true,
            "add" => true,
            "role" => RoleEnum::super,
            "permission" => "logs"
        ]);

        $this->countries();
    }
    // Add list of languages here
    public function languages(): void
    {
        Language::factory()->create([
            "name" => "en"
        ]);
        Language::factory()->create([
            "name" => "ps"
        ]);
        Language::factory()->create([
            "name" => "fa"
        ]);
    }
    // Add list of countries here
    public function countries(): void
    {
        $country = [
            "Afghanistan" => [
                "fa" => "افغانستان",
                "ps" => "افغانستان",
                "provinces" => [
                    "Kabul" => [
                        "fa" => "کابل",
                        "ps" => "کابل",
                        "District" => [
                            "Paghman" => ["fa" => "پغمان", "ps" => "پغمان"],
                            "Shakardara" => ["fa" => "شکردره", "ps" => "شکردره"],
                            "Kabul" => ["fa" => "کابل", "ps" => "کابل"],
                            "Chahar Asyab" => ["fa" => "چهاراسیاب", "ps" => "څلور اسیاب"],
                            "Deylaman" => ["fa" => "دیلمان", "ps" => "دیلمان"],
                            "Surobi" => ["fa" => "سرابی", "ps" => "سرابی"],
                            "Bagrami" => ["fa" => "بگرام", "ps" => "بگرام"],
                        ]
                    ],
                    "Herat" => [
                        "fa" => "هرات",
                        "ps" => "هرات",
                        "District" => [
                            "Herat" => ["fa" => "هرات", "ps" => "هرات"],
                            "Ghorian" => ["fa" => "غوریان", "ps" => "غوریان"],
                            "Shindand" => ["fa" => "شندند", "ps" => "شندند"],
                            "Karukh" => ["fa" => "کرخ", "ps" => "کرخ"],
                            "Pashtun Zarghun" => ["fa" => "پشتون زرغون", "ps" => "پشتون زرغون"],
                            "Gulran" => ["fa" => "گلران", "ps" => "گلران"],
                        ]
                    ],
                    "Balkh" => [
                        "fa" => "بلخ",
                        "ps" => "بلخ",
                        "District" => [
                            "Mazar-e Sharif" => ["fa" => "مزار شریف", "ps" => "مزار شریف"],
                            "Chahar Kint" => ["fa" => "چهارکنت", "ps" => "څلورکنت"],
                            "Sholgara" => ["fa" => "شولگره", "ps" => "شولگره"],
                            "Kaldar" => ["fa" => "قلدر", "ps" => "قلدر"],
                            "Zari" => ["fa" => "زاری", "ps" => "زاری"],
                        ]
                    ],
                    "Kandahar" => [
                        "fa" => "کندهار",
                        "ps" => "کندهار",
                        "District" => [
                            "Kandahar" => ["fa" => "کندهار", "ps" => "کندهار"],
                            "Dand" => ["fa" => "دند", "ps" => "دند"],
                            "Panjwayi" => ["fa" => "پنجوایی", "ps" => "پنجوایی"],
                            "Shah Wali Kot" => ["fa" => "شاه ولیکوت", "ps" => "شاه ولیکوت"],
                            "Zhari" => ["fa" => "ژړی", "ps" => "ژړی"],
                        ]
                    ],
                    "Nangarhar" => [
                        "fa" => "ننگرهار",
                        "ps" => "ننګرهار",
                        "District" => [
                            "Jalalabad" => ["fa" => "جلال آباد", "ps" => "جلال آباد"],
                            "Behsood" => ["fa" => "بهسود", "ps" => "بهسود"],
                            "Surkh Rod" => ["fa" => "سرخ رود", "ps" => "سرخ رود"],
                            "Nazi Bagh" => ["fa" => "نازی باغ", "ps" => "نازی باغ"],
                            "Khogiyani" => ["fa" => "خوگیانی", "ps" => "خوگیانی"],
                        ]
                    ],
                    "Logar" => [
                        "fa" => "لوگر",
                        "ps" => "لوګر",
                        "District" => [
                            "Pul-e Alam" => ["fa" => "پُل علم", "ps" => "پُل علم"],
                            "Kharwar" => ["fa" => "خرور", "ps" => "خرور"],
                            "Mohammad Agha" => ["fa" => "محمد آغی", "ps" => "محمد آغی"],
                            "Baraki Barak" => ["fa" => "برکی برک", "ps" => "برکی برک"],
                        ]
                    ],
                    "Ghazni" => [
                        "fa" => "غزنی",
                        "ps" => "غزنی",
                        "District" => [
                            "Ghazni" => ["fa" => "غزنی", "ps" => "غزنی"],
                            "Jaghori" => ["fa" => "جاغوری", "ps" => "جاغوری"],
                            "Qarabagh" => ["fa" => "قره باغ", "ps" => "قره باغ"],
                            "Wagaz" => ["fa" => "وجه", "ps" => "وجه"],
                        ]
                    ],
                    "Badakhshan" => [
                        "fa" => "بدخشان",
                        "ps" => "بدخشان",
                        "District" => [
                            "Faizabad" => ["fa" => "فیض آباد", "ps" => "فیض آباد"],
                            "Yawan" => ["fa" => "یوان", "ps" => "یوان"],
                            "Khwahan" => ["fa" => "خوایان", "ps" => "خوایان"],
                            "Shahriyir" => ["fa" => "شاه رییر", "ps" => "شاه رییر"],
                        ]
                    ],
                    "Bamyan" => [
                        "fa" => "بامیان",
                        "ps" => "بامیان",
                        "District" => [
                            "Bamyan" => ["fa" => "بامیان", "ps" => "بامیان"],
                            "Waras" => ["fa" => "وراز", "ps" => "وراز"],
                            "Saighan" => ["fa" => "سایغان", "ps" => "سایغان"],
                        ]
                    ],
                    "Samangan" => [
                        "fa" => "سمنگان",
                        "ps" => "سمنگان",
                        "District" => [
                            "Aybak" => ["fa" => "ایبک", "ps" => "ایبک"],
                            "Kohistan" => ["fa" => "کوهستان", "ps" => "کوهستان"],
                            "Dahana-i-Ghori" => ["fa" => "دهن غوری", "ps" => "دهن غوری"],
                        ]
                    ],
                    "Takhar" => [
                        "fa" => "تخار",
                        "ps" => "تخار",
                        "District" => [
                            "Taloqan" => ["fa" => "تالقان", "ps" => "تالقان"],
                            "Dasht Qala" => ["fa" => "داشتی قلعه", "ps" => "داشتی قلعه"],
                            "Khwaja Ghar" => ["fa" => "خواجه غار", "ps" => "خواجه غار"],
                            "Yangi Qala" => ["fa" => "یونی قلعه", "ps" => "یونی قلعه"],
                        ]
                    ],
                    "Paktia" => [
                        "fa" => "پکتیا",
                        "ps" => "پکتیا",
                        "District" => [
                            "Gardez" => ["fa" => "ګردیز", "ps" => "ګردیز"],
                            "Zadran" => ["fa" => "زرګان", "ps" => "زرګان"],
                            "Dand Wa Patan" => ["fa" => "دند و پتان", "ps" => "دند و پتان"],
                        ]
                    ],
                    "Khost" => [
                        "fa" => "خوست",
                        "ps" => "خوست",
                        "District" => [
                            "Khost" => ["fa" => "خوست", "ps" => "خوست"],
                            "Mandozai" => ["fa" => "مندوزی", "ps" => "مندوزی"],
                            "Zazai Maidan" => ["fa" => "زازای میدان", "ps" => "زازای میدان"],
                        ]
                    ],
                    "Paktika" => [
                        "fa" => "پکتیکا",
                        "ps" => "پکتیکا",
                        "District" => [
                            "Sharan" => ["fa" => "شرن", "ps" => "شرن"],
                            "Sarobi" => ["fa" => "سروری", "ps" => "سروری"],
                            "Barmal" => ["fa" => "برمل", "ps" => "برمل"],
                        ]
                    ],
                    "Nimroz" => [
                        "fa" => "نمروز",
                        "ps" => "نمروز",
                        "District" => [
                            "Zaranj" => ["fa" => "زرنج", "ps" => "زرنج"],
                            "Khash Rod" => ["fa" => "خرش رود", "ps" => "خرش رود"],
                        ]
                    ],
                    "Urozgan" => [
                        "fa" => "اُروزگان",
                        "ps" => "اُروزگان",
                        "District" => [
                            "Tarin Kot" => ["fa" => "ترین کوټ", "ps" => "ترین کوټ"],
                            "Deh Rawud" => ["fa" => "ده راود", "ps" => "ده راود"],
                        ]
                    ],
                    "Daykundi" => [
                        "fa" => "دایکندی",
                        "ps" => "دایکندی",
                        "District" => [
                            "Nili" => ["fa" => "نیلی", "ps" => "نیلی"],
                            "Kiti" => ["fa" => "کتی", "ps" => "کتی"],
                        ]
                    ],
                    "Badghis" => [
                        "fa" => "بدخشانی",
                        "ps" => "بدخشانی",
                        "District" => [
                            "Qala-i-Naw" => ["fa" => "قلعه نو", "ps" => "قلعه نو"],
                            "Murghab" => ["fa" => "مرغاب", "ps" => "مرغاب"],
                            "Jawand" => ["fa" => "جواند", "ps" => "جواند"],
                        ]
                    ],
                    "Ghor" => [
                        "fa" => "غور",
                        "ps" => "غور",
                        "District" => [
                            "Chaghcharan" => ["fa" => "چغچران", "ps" => "چغچران"],
                            "Lal wa Sarjangal" => ["fa" => "لال و سرجنگل", "ps" => "لال و سرجنگل"],
                        ]
                    ],
                    "Sar-e Pol" => [
                        "fa" => "سرپل",
                        "ps" => "سرپل",
                        "District" => [
                            "Sar-e Pol" => ["fa" => "سرپل", "ps" => "سرپل"],
                            "Kohistanat" => ["fa" => "کوهستانات", "ps" => "کوهستانات"],
                        ]
                    ],
                    "Faryab" => [
                        "fa" => "فاریاب",
                        "ps" => "فاریاب",
                        "District" => [
                            "Maymana" => ["fa" => "مینه", "ps" => "مینه"],
                            "Andkhoi" => ["fa" => "اندخوی", "ps" => "اندخوی"],
                            "Ghowchak" => ["fa" => "غوچک", "ps" => "غوچک"],
                        ]
                    ],
                    "Panjshir" => [
                        "fa" => "پنجشیر",
                        "ps" => "پنجشیر",
                        "District" => [
                            "Bazarak" => ["fa" => "بازارک", "ps" => "بازارک"],
                            "Shahristan" => ["fa" => "شهریستان", "ps" => "شهریستان"],
                        ]
                    ],
                ]
            ],
            "Albania" => [
                "fa" => "آلبانی",
                "ps" => "آلبانی",
            ],
            "Algeria" => [
                "fa" => "الجزایر",
                "ps" => "الجزایر",
            ],
            "Andorra" => [
                "fa" => "اندورا",
                "ps" => "اندورا",
            ],
            "Angola" => [
                "fa" => "انگولا",
                "ps" => "انگولا",
            ],
            "Argentina" => [
                "fa" => "آرژانتین",
                "ps" => "آرژانتین",
            ],
            "Armenia" => [
                "fa" => "ارمنستان",
                "ps" => "ارمنستان",
            ],
            "Australia" => [
                "fa" => "استرالیا",
                "ps" => "استرالیا",
            ],
            "Austria" => [
                "fa" => "اتریش",
                "ps" => "اتریش",
            ],
            "Azerbaijan" => [
                "fa" => "آذربایجان",
                "ps" => "آذربایجان",
            ],
            "Bahamas" => [
                "fa" => "باهاماس",
                "ps" => "باهاماس",
            ],
            "Bahrain" => [
                "fa" => "بحرین",
                "ps" => "بحرین",
            ],
            "Bangladesh" => [
                "fa" => "بنگلادش",
                "ps" => "بنگلادش",
            ],
            "Barbados" => [
                "fa" => "باربادوس",
                "ps" => "باربادوس",
            ],
            "Belarus" => [
                "fa" => "بلاروس",
                "ps" => "بلاروس",
            ],
            "Belgium" => [
                "fa" => "بلژیک",
                "ps" => "بلژیک",
            ],
            "Belize" => [
                "fa" => "بلیز",
                "ps" => "بلیز",
            ],
            "Benin" => [
                "fa" => "بنین",
                "ps" => "بنین",
            ],
            "Bhutan" => [
                "fa" => "بوتان",
                "ps" => "بوتان",
            ],
            "Bolivia" => [
                "fa" => "بولیوی",
                "ps" => "بولیوی",
            ],
            "Bosnia and Herzegovina" => [
                "fa" => "بوسنی و هرزگوین",
                "ps" => "بوسنی و هرزگوین",
            ],
            "Botswana" => [
                "fa" => "بوتسوانا",
                "ps" => "بوتسوانا",
            ],
            "Brazil" => [
                "fa" => "برازیل",
                "ps" => "برازیل",
            ],
            "Brunei" => [
                "fa" => "برونئی",
                "ps" => "برونئی",
            ],
            "Bulgaria" => [
                "fa" => "بلغاریا",
                "ps" => "بلغاریا",
            ],
            "Burkina Faso" => [
                "fa" => "بورکینافاسو",
                "ps" => "بورکینافاسو",
            ],
            "Burundi" => [
                "fa" => "بوروندی",
                "ps" => "بوروندی",
            ],
            "Cabo Verde" => [
                "fa" => "کابو وردی",
                "ps" => "کابو وردی",
            ],
            "Cambodia" => [
                "fa" => "کامبوج",
                "ps" => "کامبوج",
            ],
            "Cameroon" => [
                "fa" => "کامرون",
                "ps" => "کامرون",
            ],
            "Canada" => [
                "fa" => "کانادا",
                "ps" => "کانادا",
            ],
            "Central African Republic" => [
                "fa" => "جمهوری آفریقای مرکزی",
                "ps" => "جمهوری آفریقای مرکزی",
            ],
            "Chad" => [
                "fa" => "چاد",
                "ps" => "چاد",
            ],
            "Chile" => [
                "fa" => "شیلی",
                "ps" => "شیلی",
            ],
            "China" => [
                "fa" => "چین",
                "ps" => "چین",
            ],
            "Colombia" => [
                "fa" => "کلمبیا",
                "ps" => "کلمبیا",
            ],
            "Comoros" => [
                "fa" => "کومور",
                "ps" => "کومور",
            ],
            "Congo, Democratic Republic of the" => [
                "fa" => "جمهوری دموکراتیک کنگو",
                "ps" => "جمهوری دموکراتیک کنگو",
            ],
            "Congo, Republic of the" => [
                "fa" => "جمهوری کنگو",
                "ps" => "جمهوری کنگو",
            ],
            "Costa Rica" => [
                "fa" => "کاستاریکا",
                "ps" => "کاستاریکا",
            ],
            "Croatia" => [
                "fa" => "کرواسی",
                "ps" => "کرواسی",
            ],
            "Cuba" => [
                "fa" => "کیوبا",
                "ps" => "کیوبا",
            ],
            "Cyprus" => [
                "fa" => "قبرس",
                "ps" => "قبرس",
            ],
            "Czech Republic" => [
                "fa" => "جمهوری چک",
                "ps" => "جمهوری چک",
            ],
            "Denmark" => [
                "fa" => "دانمارک",
                "ps" => "دانمارک",
            ],
            "Djibouti" => [
                "fa" => "جیبوتی",
                "ps" => "جیبوتی",
            ],
            "Dominica" => [
                "fa" => "دومینیکا",
                "ps" => "دومینیکا",
            ],
            "Dominican Republic" => [
                "fa" => "جمهوری دومینیکن",
                "ps" => "جمهوری دومینیکن",
            ],
            "Ecuador" => [
                "fa" => "اکوادور",
                "ps" => "اکوادور",
            ],
            "Egypt" => [
                "fa" => "مصر",
                "ps" => "مصر",
            ],
            "El Salvador" => [
                "fa" => "السالوادور",
                "ps" => "السالوادور",
            ],
            "Equatorial Guinea" => [
                "fa" => "گینه استوایی",
                "ps" => "گینه استوایی",
            ],
            "Eritrea" => [
                "fa" => "اریتره",
                "ps" => "اریتره",
            ],
            "Estonia" => [
                "fa" => "استونی",
                "ps" => "استونی",
            ],
            "Eswatini" => [
                "fa" => "اسواتینی",
                "ps" => "اسواتینی",
            ],
            "Ethiopia" => [
                "fa" => "اتیوپی",
                "ps" => "اتیوپی",
            ],
            "Fiji" => [
                "fa" => "فیجی",
                "ps" => "فیجی",
            ],
            "Finland" => [
                "fa" => "فنلند",
                "ps" => "فنلند",
            ],
            "France" => [
                "fa" => "فرانسه",
                "ps" => "فرانسه",
            ],
            "Gabon" => [
                "fa" => "گابن",
                "ps" => "گابن",
            ],
            "Gambia" => [
                "fa" => "گامبیا",
                "ps" => "گامبیا",
            ],
            "Georgia" => [
                "fa" => "گرجستان",
                "ps" => "گرجستان",
            ],
            "Germany" => [
                "fa" => "جرمنی",
                "ps" => "جرمنی",
            ],
            "Ghana" => [
                "fa" => "غنا",
                "ps" => "غنا",
            ],
            "Greece" => [
                "fa" => "یونان",
                "ps" => "یونان",
            ],
            "Grenada" => [
                "fa" => "گرانادا",
                "ps" => "گرانادا",
            ],
            "Guatemala" => [
                "fa" => "گواتمالا",
                "ps" => "گواتمالا",
            ],
            "Guinea" => [
                "fa" => "گینه",
                "ps" => "گینه",
            ],
            "Guinea-Bissau" => [
                "fa" => "گینه بیسائو",
                "ps" => "گینه بیسائو",
            ],
            "Guyana" => [
                "fa" => "گویانا",
                "ps" => "گویانا",
            ],
            "Haiti" => [
                "fa" => "هائیتی",
                "ps" => "هائیتی",
            ],
            "Honduras" => [
                "fa" => "هندوراس",
                "ps" => "هندوراس",
            ],
            "Hungary" => [
                "fa" => "مجارستان",
                "ps" => "مجارستان",
            ],
            "Iceland" => [
                "fa" => "ایسلند",
                "ps" => "ایسلند",
            ],
            "India" => [
                "fa" => "هند",
                "ps" => "هند",
            ],
            "Indonesia" => [
                "fa" => "اندونزی",
                "ps" => "اندونزی",
            ],
            "Iran" => [
                "fa" => "ایران",
                "ps" => "ایران",
            ],
            "Iraq" => [
                "fa" => "عراق",
                "ps" => "عراق",
            ],
            "Ireland" => [
                "fa" => "ایرلند",
                "ps" => "ایرلند",
            ],
            "Israel" => [
                "fa" => "اسرائیل",
                "ps" => "اسرائیل",
            ],
            "Italy" => [
                "fa" => "ایتالیا",
                "ps" => "ایتالیا",
            ],
            "Jamaica" => [
                "fa" => "جامائیکا",
                "ps" => "جامائیکا",
            ],
            "Japan" => [
                "fa" => "جاپان",
                "ps" => "جاپان",
            ],
            "Jordan" => [
                "fa" => "اردن",
                "ps" => "اردن",
            ],
            "Kazakhstan" => [
                "fa" => "قزاقستان",
                "ps" => "قزاقستان",
            ],
            "Kenya" => [
                "fa" => "کنیا",
                "ps" => "کنیا",
            ],
            "Kiribati" => [
                "fa" => "کیریباتی",
                "ps" => "کیریباتی",
            ],
            "Kuwait" => [
                "fa" => "کویت",
                "ps" => "کویت",
            ],
            "Kyrgyzstan" => [
                "fa" => "قرقیزستان",
                "ps" => "قرقیزستان",
            ],
            "Laos" => [
                "fa" => "لاوس",
                "ps" => "لاوس",
            ],
            "Latvia" => [
                "fa" => "لتونی",
                "ps" => "لتونی",
            ],
            "Lebanon" => [
                "fa" => "لبنان",
                "ps" => "لبنان",
            ],
            "Lesotho" => [
                "fa" => "لسوتو",
                "ps" => "لسوتو",
            ],
            "Liberia" => [
                "fa" => "لیبریا",
                "ps" => "لیبریا",
            ],
            "Libya" => [
                "fa" => "لیبیا",
                "ps" => "لیبیا",
            ],
            "Liechtenstein" => [
                "fa" => "لیختن‌اشتاین",
                "ps" => "لیختن‌اشتاین",
            ],
            "Lithuania" => [
                "fa" => "لیتوانی",
                "ps" => "لیتوانی",
            ],
            "Luxembourg" => [
                "fa" => "لوکزامبورگ",
                "ps" => "لوکزامبورگ",
            ],
            "Madagascar" => [
                "fa" => "ماداگاسکار",
                "ps" => "ماداگاسکار",
            ],
            "Malawi" => [
                "fa" => "مالاوی",
                "ps" => "مالاوی",
            ],
            "Malaysia" => [
                "fa" => "مالزی",
                "ps" => "مالزی",
            ],
            "Maldives" => [
                "fa" => "مالدیو",
                "ps" => "مالدیو",
            ],
            "Mali" => [
                "fa" => "مالی",
                "ps" => "مالی",
            ],
            "Malta" => [
                "fa" => "مالت",
                "ps" => "مالت",
            ],
            "Marshall Islands" => [
                "fa" => "جزایر مارشال",
                "ps" => "جزایر مارشال",
            ],
            "Mauritania" => [
                "fa" => "موریطانی",
                "ps" => "موریطانی",
            ],
            "Mauritius" => [
                "fa" => "موریس",
                "ps" => "موریس",
            ],
            "Mexico" => [
                "fa" => "مکسیکو",
                "ps" => "مکسیکو",
            ],
            "Micronesia" => [
                "fa" => "میکرونزی",
                "ps" => "میکرونزی",
            ],
            "Moldova" => [
                "fa" => "مولداوی",
                "ps" => "مولداوی",
            ],
            "Monaco" => [
                "fa" => "موناكو",
                "ps" => "موناكو",
            ],
            "Mongolia" => [
                "fa" => "مغولستان",
                "ps" => "مغولستان",
            ],
            "Montenegro" => [
                "fa" => "مونته‌نگرو",
                "ps" => "مونته‌نگرو",
            ],
            "Morocco" => [
                "fa" => "مراکش",
                "ps" => "مراکش",
            ],
            "Mozambique" => [
                "fa" => "موزامبیک",
                "ps" => "موزامبیک",
            ],
            "Myanmar" => [
                "fa" => "میانمار",
                "ps" => "میانمار",
            ],
            "Namibia" => [
                "fa" => "نامیبیا",
                "ps" => "نامیبیا",
            ],
            "Nauru" => [
                "fa" => "ناورو",
                "ps" => "ناورو",
            ],
            "Nepal" => [
                "fa" => "نیپال",
                "ps" => "نیپال",
            ],
            "Netherlands" => [
                "fa" => "هلند",
                "ps" => "هلند",
            ],
            "New Zealand" => [
                "fa" => "نیوزیلند",
                "ps" => "نیوزیلند",
            ],
            "Nicaragua" => [
                "fa" => "نیکاراگوئه",
                "ps" => "نیکاراگوئه",
            ],
            "Niger" => [
                "fa" => "نیجر",
                "ps" => "نیجر",
            ],
            "Nigeria" => [
                "fa" => "نیجریا",
                "ps" => "نیجریا",
            ],
            "North Macedonia" => [
                "fa" => "مقدونیه شمالی",
                "ps" => "مقدونیه شمالی",
            ],
            "Norway" => [
                "fa" => "نروژ",
                "ps" => "نروژ",
            ],
            "Oman" => [
                "fa" => "عمان",
                "ps" => "عمان",
            ],
            "Pakistan" => [
                "fa" => "پاکستان",
                "ps" => "پاکستان",
            ],
            "Palau" => [
                "fa" => "پالائو",
                "ps" => "پالائو",
            ],
            "Palestine" => [
                "fa" => "فلسطین",
                "ps" => "فلسطین",
            ],
            "Panama" => [
                "fa" => "پاناما",
                "ps" => "پاناما",
            ],
            "Papua New Guinea" => [
                "fa" => "پاپوآ گینه نو",
                "ps" => "پاپوآ گینه نو",
            ],
            "Paraguay" => [
                "fa" => "پاراگوئه",
                "ps" => "پاراگوئه",
            ],
            "Peru" => [
                "fa" => "پرو",
                "ps" => "پرو",
            ],
            "Philippines" => [
                "fa" => "فیلیپین",
                "ps" => "فیلیپین",
            ],
            "Poland" => [
                "fa" => "لهستان",
                "ps" => "لهستان",
            ],
            "Portugal" => [
                "fa" => "پرتغال",
                "ps" => "پرتغال",
            ],
            "Qatar" => [
                "fa" => "قطر",
                "ps" => "قطر",
            ],
            "Romania" => [
                "fa" => "رومانی",
                "ps" => "رومانی",
            ],
            "Russia" => [
                "fa" => "روسیه",
                "ps" => "روسیه",
            ],
            "Rwanda" => [
                "fa" => "رواندا",
                "ps" => "رواندا",
            ],
            "Saint Kitts and Nevis" => [
                "fa" => "سنت کیتس و نویس",
                "ps" => "سنت کیتس و نویس",
            ],
            "Saint Lucia" => [
                "fa" => "سنت لوسیا",
                "ps" => "سنت لوسیا",
            ],
            "Saint Vincent and the Grenadines" => [
                "fa" => "سنت وینسنت و گرنادین",
                "ps" => "سنت وینسنت و گرنادین",
            ],
            "Samoa" => [
                "fa" => "ساموآ",
                "ps" => "ساموآ",
            ],
            "San Marino" => [
                "fa" => "سان مارینو",
                "ps" => "سان مارینو",
            ],
            "Sao Tome and Principe" => [
                "fa" => "سائوتومه و پرنسیپ",
                "ps" => "سائوتومه و پرنسیپ",
            ],
            "Saudi Arabia" => [
                "fa" => "عربستان سعودی",
                "ps" => "عربستان سعودی",
            ],
            "Senegal" => [
                "fa" => "سنگال",
                "ps" => "سنگال",
            ],
            "Serbia" => [
                "fa" => "صربستان",
                "ps" => "صربستان",
            ],
            "Seychelles" => [
                "fa" => "سیشل",
                "ps" => "سیشل",
            ],
            "Sierra Leone" => [
                "fa" => "سیرالئون",
                "ps" => "سیرالئون",
            ],
            "Singapore" => [
                "fa" => "سنگاپور",
                "ps" => "سنگاپور",
            ],
            "Slovakia" => [
                "fa" => "اسلواکی",
                "ps" => "اسلواکی",
            ],
            "Slovenia" => [
                "fa" => "اسلوونی",
                "ps" => "اسلوونی",
            ],
            "Solomon Islands" => [
                "fa" => "جزایر سلیمان",
                "ps" => "جزایر سلیمان",
            ],
            "Somalia" => [
                "fa" => "سومالی",
                "ps" => "سومالی",
            ],
            "South Africa" => [
                "fa" => "آفریقای جنوبی",
                "ps" => "آفریقای جنوبی",
            ],
            "South Korea" => [
                "fa" => "کره جنوبی",
                "ps" => "کره جنوبی",
            ],
            "South Sudan" => [
                "fa" => "جنوب سودان",
                "ps" => "جنوب سودان",
            ],
            "Spain" => [
                "fa" => "اسپانیا",
                "ps" => "اسپانیا",
            ],
            "Sri Lanka" => [
                "fa" => "سریلانکا",
                "ps" => "سریلانکا",
            ],
            "Sudan" => [
                "fa" => "سودان",
                "ps" => "سودان",
            ],
            "Suriname" => [
                "fa" => "سورینام",
                "ps" => "سورینام",
            ],
            "Sweden" => [
                "fa" => "سوئد",
                "ps" => "سوئد",
            ],
            "Switzerland" => [
                "fa" => "سویس",
                "ps" => "سویس",
            ],
            "Syria" => [
                "fa" => "سوریه",
                "ps" => "سوریه",
            ],
            "Tajikistan" => [
                "fa" => "تاجیکستان",
                "ps" => "تاجیکستان",
            ],
            "Tanzania" => [
                "fa" => "تانزانیا",
                "ps" => "تانزانیا",
            ],
            "Thailand" => [
                "fa" => "تایلند",
                "ps" => "تایلند",
            ],
            "Togo" => [
                "fa" => "توگو",
                "ps" => "توگو",
            ],
            "Tonga" => [
                "fa" => "تونگا",
                "ps" => "تونگا",
            ],
            "Trinidad and Tobago" => [
                "fa" => "ترینیداد و توباگو",
                "ps" => "ترینیداد و توباگو",
            ],
            "Tunisia" => [
                "fa" => "تونس",
                "ps" => "تونس",
            ],
            "Turkey" => [
                "fa" => "ترکیه",
                "ps" => "ترکیه",
            ],
            "Turkmenistan" => [
                "fa" => "ترکمنستان",
                "ps" => "ترکمنستان",
            ],
            "Tuvalu" => [
                "fa" => "تووالو",
                "ps" => "تووالو",
            ],
            "Uganda" => [
                "fa" => "اوگاندا",
                "ps" => "اوگاندا",
            ],
            "Ukraine" => [
                "fa" => "اوکراین",
                "ps" => "اوکراین",
            ],
            "United Arab Emirates" => [
                "fa" => "امارات متحده عربی",
                "ps" => "امارات متحده عربی",
            ],
            "United Kingdom" => [
                "fa" => "پادشاهی متحده",
                "ps" => "متحده ملک",
            ],
            "United States" => [
                "fa" => "ایالات متحده",
                "ps" => "متحده ایالات",
            ],
            "Uruguay" => [
                "fa" => "اورگوئه",
                "ps" => "اورگوئه",
            ],
            "Uzbekistan" => [
                "fa" => "ازبکستان",
                "ps" => "ازبکستان",
            ],
            "Vanuatu" => [
                "fa" => "وانواتو",
                "ps" => "وانواتو",
            ],
            "Vatican City" => [
                "fa" => "شهر واتیکان",
                "ps" => "شهر واتیکان",
            ],
            "Venezuela" => [
                "fa" => "ونزوئلا",
                "ps" => "ونزوئلا",
            ],
            "Vietnam" => [
                "fa" => "ویتنام",
                "ps" => "ویتنام",
            ],
            "Yemen" => [
                "fa" => "یمن",
                "ps" => "یمن",
            ],
            "Zambia" => [
                "fa" => "زامبیا",
                "ps" => "زامبیا",
            ],
            "Zimbabwe" => [
                "fa" => "زیمبابوه",
                "ps" => "زیمبابوه",
            ],
        ];

        foreach ($country as $name => $translations) {
            // Create the country record
            $cnt = Country::factory()->create([
                "name" => $name
            ]);

            // Loop through translations (e.g., fa, ps)
            foreach ($translations as $key => $value) {
                // Check if this is the province section
                if ($key == 'provinces') {
                    foreach ($value as $provinceName => $provinceDetails) {
                        // Create a province for this country
                        $province = Province::factory()->create([
                            "name" => $provinceName,
                            "country_id" => $cnt->id,  // Associate province with the created country
                        ]);

                        // Loop through the province's translations and districts
                        foreach ($provinceDetails as $provinceKey => $provinceValue) {
                            if ($provinceKey == 'District') {
                                foreach ($provinceValue as $districtName => $districtDetails) {
                                    // Create district for this province
                                    $district = District::factory()->create([
                                        "name" => $districtName,
                                        "province_id" => $province->id,  // Associate district with the created province
                                    ]);

                                    // Translate district details (e.g., fa, ps)
                                    foreach ($districtDetails as $language => $translation) {
                                        $this->Translate($translation, $language, $district->id, District::class);
                                    }
                                }
                            } else {
                                // Translate province details (e.g., fa, ps)
                                $this->Translate($provinceValue, $provinceKey, $province->id, Province::class);
                            }
                        }
                    }
                } else {
                    // Translate country details (e.g., fa, ps)
                    $this->Translate($value, $key, $cnt->id, Country::class);
                }
            }
        }
    }

    // Add list of districts here
    public function Translate($value, $language, $translable_id, $translable_type): void
    {
        Translate::factory()->create([
            "value" => $value,
            "language_name" => $language,
            "translable_type" => $translable_type,
            "translable_id" => $translable_id,
        ]);
    }
}

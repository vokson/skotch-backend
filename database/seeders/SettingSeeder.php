<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Setting;
use App\Status;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = [
            'TOKEN_LIFE_TIME' => '43200',
            'DEFAULT_PASSWORD' => '1234',
            'COUNT_OF_ITEMS_IN_NEWS' => '20',
            'ARCHIVE_STORAGE_TIME' => '1800',
            'ARCHIVE_CREATION_TIME' => '300',
            'RATING_MISTAKE_COUNT' => '12',
            'RATING_MISTAKE_PROBABILITY' => '0.95',
            'RATING_INITIAL_VALUE' => '0.5',
            'LAST_TASK_NUMBER' => '001',
            'CREATE_TASK_NOTIFICATION' => 'Создан TASK',
            // Одинарное экранирование, так как это PHP регулярное выражение
            'CHECKER_REG_EXP_FOR_NEW_FILE' => '/^4022-[A-Z]{2}-[A-Z]{2}-(00000|66210|66220|66230|66321|66340|66341|66422|66450|66560|66570|66580|66690)(\d{4}|\d{2}-\d{3}|\d{1}-\d{3}(\s[A-Z]\d?)?)-IS\d{1}[A-Z0-9]{1}(_R)?(_C_RH_(OP|CL))?\.(dwg|DWG|pdf|PDF|doc|DOC|docx|DOCX|xls|XLS|xlsx|XLSX|zip|ZIP){1}$/',
            'CHECKER_REG_EXP_FOR_CHECKED_FILE' => '/^4022-[A-Z]{2}-[A-Z]{2}-(00000|66210|66220|66230|66321|66340|66341|66422|66450|66560|66570|66580|66690)(\d{4}|\d{2}-\d{3}|\d{1}-\d{3}(\s[A-Z]\d?)?)-IS\d{1}[A-Z0-9]{1}(_R)?(_C_RH_(OP|CL))?\[\d+\]\.(dwg|DWG|pdf|PDF|doc|DOC|docx|DOCX|xls|XLS|xlsx|XLSX|zip|ZIP){1}$/',
            'DOCS_REG_EXP_FOR_LIST_FILE' => '/.+\.json$/',
            'TRANSMITTAL_REG_EXP' => '/^(4022-CM-RH-T-|4022-RH-CM-T-)\d{3}[1-9]{1}$/',
            'DOCS_REG_EXP_FOR_PDF_FILE' => '/.*(pdf|PDF)$/',
            'STATUS_ID_FOR_NEW_TRANSMITTAL' => Status::where('name', 'TRANSMITTAL')->first()->id,
            'SENDER_FOLDER_NOTIFICATION_SUBJECT' => 'ВОЛГАФЕРТ. ОТПРАВКА.',
            'FRONTEND_PROJECT_NAME_RU' => 'Проектный офис ВОЛГАФЕРТ',
            'FRONTEND_PROJECT_NAME_EN' => 'Design office VOLGAFERT',
            'FRONTEND_CODE_1_NAME_RU' => 'КОД ДОКУМЕНТА 1',
            'FRONTEND_CODE_1_NAME_EN' => 'DOC CODE 1',
            'FRONTEND_CODE_2_NAME_RU' => 'КОД ДОКУМЕНТА 2',
            'FRONTEND_CODE_2_NAME_EN' => 'DOC CODE 2',
            // Двойное экранирование, так как это уже JS регулярное выражение
            'FRONTEND_DOCS_CODE_1_JS_REG_EXP' => '^4022-[A-Z]{2}-[A-Z]{2}-(00000|66210|66220|66230|66321|66340|66341|66422|66450|66560|66570|66580|66690)(\\d{4}|\\d{2}-\\d{3}|\\d{1}-\\d{3}(\\s[A-Z]\\d?)?)(_C_RH_(OP|CL))?$',
            'FRONTEND_DOCS_CODE_2_JS_REG_EXP' => '^7500081106-(00000|66210|66220|66230|66321|66340|66341|66422|66450|66560|66570|66580|66690)-(КМ|КЖ|АР|ЭГ|НВК|НВК|ОВ)\\d{0,2}(\\.(РР|ТИ|ТЗ))?-\\d{4}$',
            'DOCS_REV_LIST' => '-|0A|0B|0C|0D|0E|0F|0G|0H|00|1A|1B|1C|1D|1E|1F|1G|1H|01|2A|2B|2C|2D|2E|2F|2G|2H|02|3A|3B|3C|3D|3E|3F|3G|3H|03|4A|4B|4C|4D|4E|4F|4G|4H|04|5A|5B|5C|5D|5E|5F|5G|5H|05|6A|6B|6C|6D|6E|6F|6G|6H|06|7A|7B|7C|7D|7E|7F|7G|7H|07|8A|8B|8C|8D|8E|8F|8G|8H|08|9A|9B|9C|9D|9E|9F|9G|9H|09|10A|10B|10C|10D|10E|10F|10G|10H|10|11A|11B|11C|11D|11E|11F|11G|11H|11|12A|12B|12C|12D|12E|12F|12G|12H|12|13A|13B|13C|13D|13E|13F|13G|13H|13|14A|14B|14C|14D|14E|14F|14G|14H|14|15A|15B|15C|15D|15E|15F|15G|15H|15|16A|16B|16C|16D|16E|16F|16G|16H|16|17A|17B|17C|17D|17E|17F|17G|17H|17|18A|18B|18C|18D|18E|18F|18G|18H|18|19A|19B|19C|19D|19E|19F|19G|19H|19|20A|20B|20C|20D|20E|20F|20G|20H|20',
            'FRONTEND_DOCS_CLASS_JS_REG_EXP' => '^(A|C|I|RQ|FI|IFC)$',
            'FRONTEND_RECORDS_ADD_WORDS_TO_TEXT_IN_JSON' => '["Здесь должны","быть слова","которые можно добавить"]',
            'FRONTEND_TITLES_FIELD_1_NAME_RU' => 'Предшественник',
            'FRONTEND_TITLES_FIELD_1_NAME_EN' => 'Predecessor',
            'FRONTEND_TITLES_FIELD_2_NAME_RU' => 'Описание',
            'FRONTEND_TITLES_FIELD_2_NAME_EN' => 'Description',
            'FRONTEND_TITLES_FIELD_3_NAME_RU' => 'Объем',
            'FRONTEND_TITLES_FIELD_3_NAME_EN' => 'Volume',
            'FRONTEND_DOCS_JSON_MAX_FILE_SIZE_MB' => '1',
            'FRONTEND_RECORDS_MAX_FILE_SIZE_MB' => '1000',
            'FRONTEND_CHECKER_MAX_FILE_SIZE_MB' => '50',
            'FRONTEND_SENDER_MAX_FILE_SIZE_MB' => '1000',
            'FRONTEND_MERGE_PDF_MAX_FILE_SIZE_MB' => '100',
            // Четверное экранирование, так как сначала парсится JSON, а потом регулярное выражение передается в PHP
            'FRONTEND_CHARTS_TITLES_PHP_REG_EXP_IN_JSON' => '[{"key": "All / Все", "value": "/.*/"}, {"key": "KJ*.DRW", "value": "/.*[-]{1}KJ[\\\\d]*\\\\.DRW$/"}]',
        ];

        // update docs set revision="-" where revision NOT IN ("-", "0A","0B","0C","0D","0E","0F","0G","0H","00","1A","1B","1C","1D","1E","1F","1G","1H","01","2A","2B","2C","2D","2E","2F","2G","2H","02","3A","3B","3C","3D","3E","3F","3G","3H","03","4A","4B","4C","4D","4E","4F","4G","4H","04","5A","5B","5C","5D","5E","5F","5G","5H","05","6A","6B","6C","6D","6E","6F","6G","6H","06","7A","7B","7C","7D","7E","7F","7G","7H","07","8A","8B","8C","8D","8E","8F","8G","8H","08","9A","9B","9C","9D","9E","9F","9G","9H","09","10A","10B","10C","10D","10E","10F","10G","10H","10","11A","11B","11C","11D","11E","11F","11G","11H","11","12A","12B","12C","12D","12E","12F","12G","12H","12","13A","13B","13C","13D","13E","13F","13G","13H","13","14A","14B","14C","14D","14E","14F","14G","14H","14","15A","15B","15C","15D","15E","15F","15G","15H","15","16A","16B","16C","16D","16E","16F","16G","16H","16","17A","17B","17C","17D","17E","17F","17G","17H","17","18A","18B","18C","18D","18E","18F","18G","18H","18","19A","19B","19C","19D","19E","19F","19G","19H","19","20A","20B","20C","20D","20E","20F","20G","20H","20")

        foreach ($settings as $key => $value) {
            $action = Setting::create(['name' => $key, 'value' => $value]);
            $action->save();
        }
    }
}

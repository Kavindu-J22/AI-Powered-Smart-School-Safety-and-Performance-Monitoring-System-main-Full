<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute ක්ෂේත්‍රය පිළිගත යුතුය.',
    'accepted_if' => ':other :value වන විට :attribute ක්ෂේත්‍රය පිළිගත යුතුය.',
    'active_url' => ':attribute ක්ෂේත්‍රය වලංගු URL එකක් විය යුතුය.',
    'after' => ':attribute ක්ෂේත්‍රය :date පසු දිනයක් විය යුතුය.',
    'after_or_equal' => ':attribute ක්ෂේත්‍රය :date ට පසු හෝ සමාන දිනයක් විය යුතුය.',
    'alpha' => ':attribute ක්ෂේත්‍රයේ අකුරු පමණක් අඩංගු විය යුතුය.',
    'alpha_dash' => ':attribute ක්ෂේත්‍රයේ අකුරු, අංක, ඉරි සහ යටි ඉරි පමණක් අඩංගු විය යුතුය.',
    'alpha_num' => ':attribute ක්ෂේත්‍රයේ අකුරු සහ අංක පමණක් අඩංගු විය යුතුය.',
    'array' => ':attribute ක්ෂේත්‍රය අරාවක් විය යුතුය.',
    'ascii' => ':attribute ක්ෂේත්‍රයේ තනි-බයිට් අකුරු අංක සහ සංකේත පමණක් අඩංගු විය යුතුය.',
    'before' => ':attribute ක්ෂේත්‍රය :date පෙර දිනයක් විය යුතුය.',
    'before_or_equal' => ':attribute ක්ෂේත්‍රය :date ට පෙර හෝ සමාන දිනයක් විය යුතුය.',
    'between' => [
        'array' => ':attribute ක්ෂේත්‍රයේ :min සහ :max අතර අයිතම තිබිය යුතුය.',
        'file' => ':attribute ක්ෂේත්‍රය :min සහ :max කිලෝබයිට් අතර විය යුතුය.',
        'numeric' => ':attribute ක්ෂේත්‍රය :min සහ :max අතර විය යුතුය.',
        'string' => ':attribute ක්ෂේත්‍රය :min සහ :max අක්ෂර අතර විය යුතුය.',
    ],
    'boolean' => ':attribute ක්ෂේත්‍රය සත්‍ය හෝ අසත්‍ය විය යුතුය.',
    'can' => ':attribute ක්ෂේත්‍රයේ අනවසර අගයක් අඩංගුය.',
    'confirmed' => ':attribute ක්ෂේත්‍රය තහවුරු කිරීම නොගැලපේ.',
    'contains' => ':attribute ක්ෂේත්‍රයේ අවශ්‍ය අගයක් නොමැත.',
    'current_password' => 'මුරපදය වැරදිය.',
    'date' => ':attribute ක්ෂේත්‍රය වලංගු දිනයක් විය යුතුය.',
    'date_equals' => ':attribute ක්ෂේත්‍රය :date ට සමාන දිනයක් විය යුතුය.',
    'date_format' => ':attribute ක්ෂේත්‍රය :format ආකෘතියට ගැලපිය යුතුය.',
    'decimal' => ':attribute ක්ෂේත්‍රයේ :decimal දශම ස්ථාන තිබිය යුතුය.',
    'declined' => ':attribute ක්ෂේත්‍රය ප්‍රතික්ෂේප විය යුතුය.',
    'declined_if' => ':other :value වන විට :attribute ක්ෂේත්‍රය ප්‍රතික්ෂේප විය යුතුය.',
    'different' => ':attribute ක්ෂේත්‍රය සහ :other වෙනස් විය යුතුය.',
    'digits' => ':attribute ක්ෂේත්‍රය :digits ඉලක්කම් විය යුතුය.',
    'digits_between' => ':attribute ක්ෂේත්‍රය :min සහ :max ඉලක්කම් අතර විය යුතුය.',
    'dimensions' => ':attribute ක්ෂේත්‍රයේ රූප මානයන් වලංගු නැත.',
    'distinct' => ':attribute ක්ෂේත්‍රයේ අනුපිටපත් අගයක් ඇත.',
    'doesnt_end_with' => ':attribute ක්ෂේත්‍රය පහත සඳහන් වලින් කිසිවක් වන නොකළ යුතුය: :values.',
    'doesnt_start_with' => ':attribute ක්ෂේත්‍රය පහත සඳහන් වලින් කිසිවක් ආරම්භ නොකළ යුතුය: :values.',
    'email' => ':attribute ක්ෂේත්‍රය වලංගු ඊමේල් ලිපිනයක් විය යුතුය.',
    'ends_with' => ':attribute ක්ෂේත්‍රය පහත සඳහන් වලින් එකක් වන විය යුතුය: :values.',
    'enum' => 'තෝරාගත් :attribute අවලංගුය.',
    'exists' => 'තෝරාගත් :attribute අවලංගුය.',
    'extensions' => ':attribute ක්ෂේත්‍රයේ පහත සඳහන් දිගුවලින් එකක් තිබිය යුතුය: :values.',
    'file' => ':attribute ක්ෂේත්‍රය ගොනුවක් විය යුතුය.',
    'filled' => ':attribute ක්ෂේත්‍රයේ අගයක් තිබිය යුතුය.',
    'gt' => [
        'array' => ':attribute ක්ෂේත්‍රයේ :value ට වඩා වැඩි අයිතම තිබිය යුතුය.',
        'file' => ':attribute ක්ෂේත්‍රය :value කිලෝබයිට් ට වඩා විශාල විය යුතුය.',
        'numeric' => ':attribute ක්ෂේත්‍රය :value ට වඩා විශාල විය යුතුය.',
        'string' => ':attribute ක්ෂේත්‍රය :value අක්ෂර ට වඩා විශාල විය යුතුය.',
    ],
    'gte' => [
        'array' => ':attribute ක්ෂේත්‍රයේ :value අයිතම හෝ වැඩි ප්‍රමාණයක් තිබිය යුතුය.',
        'file' => ':attribute ක්ෂේත්‍රය :value කිලෝබයිට් ට වඩා විශාල හෝ සමාන විය යුතුය.',
        'numeric' => ':attribute ක්ෂේත්‍රය :value ට වඩා විශාල හෝ සමාන විය යුතුය.',
        'string' => ':attribute ක්ෂේත්‍රය :value අක්ෂර ට වඩා විශාල හෝ සමාන විය යුතුය.',
    ],
    'hex_color' => ':attribute ක්ෂේත්‍රය වලංගු ෂඩාශ්‍රික වර්ණයක් විය යුතුය.',
    'image' => ':attribute ක්ෂේත්‍රය රූපයක් විය යුතුය.',
    'in' => 'තෝරාගත් :attribute අවලංගුය.',
    'in_array' => ':attribute ක්ෂේත්‍රය :other හි පැවතිය යුතුය.',
    'integer' => ':attribute ක්ෂේත්‍රය පූර්ණ සංඛ්‍යාවක් විය යුතුය.',
    'ip' => ':attribute ක්ෂේත්‍රය වලංගු IP ලිපිනයක් විය යුතුය.',
    'ipv4' => ':attribute ක්ෂේත්‍රය වලංගු IPv4 ලිපිනයක් විය යුතුය.',
    'ipv6' => ':attribute ක්ෂේත්‍රය වලංගු IPv6 ලිපිනයක් විය යුතුය.',
    'json' => ':attribute ක්ෂේත්‍රය වලංගු JSON පේළියක් විය යුතුය.',
    'list' => ':attribute ක්ෂේත්‍රය ලැයිස්තුවක් විය යුතුය.',
    'lowercase' => ':attribute ක්ෂේත්‍රය කුඩා අකුරු විය යුතුය.',
    'lt' => [
        'array' => ':attribute ක්ෂේත්‍රයේ :value ට වඩා අඩු අයිතම තිබිය යුතුය.',
        'file' => ':attribute ක්ෂේත්‍රය :value කිලෝබයිට් ට වඩා අඩු විය යුතුය.',
        'numeric' => ':attribute ක්ෂේත්‍රය :value ට වඩා අඩු විය යුතුය.',
        'string' => ':attribute ක්ෂේත්‍රය :value අක්ෂර ට වඩා අඩු විය යුතුය.',
    ],
    'lte' => [
        'array' => ':attribute ක්ෂේත්‍රයේ :value ට වඩා වැඩි අයිතම නොතිබිය යුතුය.',
        'file' => ':attribute ක්ෂේත්‍රය :value කිලෝබයිට් ට වඩා අඩු හෝ සමාන විය යුතුය.',
        'numeric' => ':attribute ක්ෂේත්‍රය :value ට වඩා අඩු හෝ සමාන විය යුතුය.',
        'string' => ':attribute ක්ෂේත්‍රය :value අක්ෂර ට වඩා අඩු හෝ සමාන විය යුතුය.',
    ],
    'mac_address' => ':attribute ක්ෂේත්‍රය වලංගු MAC ලිපිනයක් විය යුතුය.',
    'max' => [
        'array' => ':attribute ක්ෂේත්‍රයේ :max ට වඩා වැඩි අයිතම නොතිබිය යුතුය.',
        'file' => ':attribute ක්ෂේත්‍රය :max කිලෝබයිට් ට වඩා විශාල නොවිය යුතුය.',
        'numeric' => ':attribute ක්ෂේත්‍රය :max ට වඩා විශාල නොවිය යුතුය.',
        'string' => ':attribute ක්ෂේත්‍රය :max අක්ෂර ට වඩා විශාල නොවිය යුතුය.',
    ],
    'max_digits' => ':attribute ක්ෂේත්‍රයේ :max ට වඩා වැඩි ඉලක්කම් නොතිබිය යුතුය.',
    'mimes' => ':attribute ක්ෂේත්‍රය :values වර්ගයේ ගොනුවක් විය යුතුය.',
    'mimetypes' => ':attribute ක්ෂේත්‍රය :values වර්ගයේ ගොනුවක් විය යුතුය.',
    'min' => [
        'array' => ':attribute ක්ෂේත්‍රයේ අවම වශයෙන් :min අයිතම තිබිය යුතුය.',
        'file' => ':attribute ක්ෂේත්‍රය අවම වශයෙන් :min කිලෝබයිට් විය යුතුය.',
        'numeric' => ':attribute ක්ෂේත්‍රය අවම වශයෙන් :min විය යුතුය.',
        'string' => ':attribute ක්ෂේත්‍රය අවම වශයෙන් :min අක්ෂර විය යුතුය.',
    ],
    'min_digits' => ':attribute ක්ෂේත්‍රයේ අවම වශයෙන් :min ඉලක්කම් තිබිය යුතුය.',
    'missing' => ':attribute ක්ෂේත්‍රය අනුපස්ථිත විය යුතුය.',
    'missing_if' => ':other :value වන විට :attribute ක්ෂේත්‍රය අනුපස්ථිත විය යුතුය.',
    'missing_unless' => ':other :value නොවන්නේ නම් :attribute ක්ෂේත්‍රය අනුපස්ථිත විය යුතුය.',
    'missing_with' => ':values පවතින විට :attribute ක්ෂේත්‍රය අනුපස්ථිත විය යුතුය.',
    'missing_with_all' => ':values පවතින විට :attribute ක්ෂේත්‍රය අනුපස්ථිත විය යුතුය.',
    'multiple_of' => ':attribute ක්ෂේත්‍රය :value හි ගුණාකරණයක් විය යුතුය.',
    'not_in' => 'තෝරාගත් :attribute අවලංගුය.',
    'not_regex' => ':attribute ක්ෂේත්‍රයේ ආකෘතිය අවලංගුය.',
    'numeric' => ':attribute ක්ෂේත්‍රය සංඛ්‍යාවක් විය යුතුය.',
    'password' => [
        'letters' => ':attribute ක්ෂේත්‍රයේ අවම වශයෙන් එක් අකුරක් තිබිය යුතුය.',
        'mixed' => ':attribute ක්ෂේත්‍රයේ අවම වශයෙන් එක් ලොකු අකුරක් සහ එක් පුංචි අකුරක් තිබිය යුතුය.',
        'numbers' => ':attribute ක්ෂේත්‍රයේ අවම වශයෙන් එක් අංකයක් තිබිය යුතුය.',
        'symbols' => ':attribute ක්ෂේත්‍රයේ අවම වශයෙන් එක් සංකේතයක් තිබිය යුතුය.',
        'uncompromised' => 'ලබා දුන් :attribute දත්ත කාන්දුවකින් සොයාගන්නා ලදී. කරුණාකර වෙනස් :attribute තෝරන්න.',
    ],
    'present' => ':attribute ක්ෂේත්‍රය පැවතිය යුතුය.',
    'present_if' => ':other :value වන විට :attribute ක්ෂේත්‍රය පැවතිය යුතුය.',
    'present_unless' => ':other :value නොවන්නේ නම් :attribute ක්ෂේත්‍රය පැවතිය යුතුය.',
    'present_with' => ':values පවතින විට :attribute ක්ෂේත්‍රය පැවතිය යුතුය.',
    'present_with_all' => ':values පවතින විට :attribute ක්ෂේත්‍රය පැවතිය යුතුය.',
    'prohibited' => ':attribute ක්ෂේත්‍රය තහනම් කර ඇත.',
    'prohibited_if' => ':other :value වන විට :attribute ක්ෂේත්‍රය තහනම් කර ඇත.',
    'prohibited_if_accepted' => ':other පිළිගත් විට :attribute ක්ෂේත්‍රය තහනම් කර ඇත.',
    'prohibited_if_declined' => ':other ප්‍රතික්ෂේප කළ විට :attribute ක්ෂේත්‍රය තහනම් කර ඇත.',
    'prohibited_unless' => ':other :values හි නොමැති නම් :attribute ක්ෂේත්‍රය තහනම් කර ඇත.',
    'prohibits' => ':attribute ක්ෂේත්‍රය :other පැවතීම තහනම් කරයි.',
    'regex' => ':attribute ක්ෂේත්‍රයේ ආකෘතිය අවලංගුය.',
    'required' => ':attribute ක්ෂේත්‍රය අවශ්‍යයි.',
    'required_array_keys' => ':attribute ක්ෂේත්‍රයේ :values සඳහා ඇතුළත් කිරීම් තිබිය යුතුය.',
    'required_if' => ':other :value වන විට :attribute ක්ෂේත්‍රය අවශ්‍යයි.',
    'required_if_accepted' => ':other පිළිගත් විට :attribute ක්ෂේත්‍රය අවශ්‍යයි.',
    'required_if_declined' => ':other ප්‍රතික්ෂේප කළ විට :attribute ක්ෂේත්‍රය අවශ්‍යයි.',
    'required_unless' => ':other :values හි නොමැති නම් :attribute ක්ෂේත්‍රය අවශ්‍යයි.',
    'required_with' => ':values පවතින විට :attribute ක්ෂේත්‍රය අවශ්‍යයි.',
    'required_with_all' => ':values පවතින විට :attribute ක්ෂේත්‍රය අවශ්‍යයි.',
    'required_without' => ':values නොමැති විට :attribute ක්ෂේත්‍රය අවශ්‍යයි.',
    'required_without_all' => ':values කිසිවක් නොමැති විට :attribute ක්ෂේත්‍රය අවශ්‍යයි.',
    'same' => ':attribute ක්ෂේත්‍රය :other ට ගැලපිය යුතුය.',
    'size' => [
        'array' => ':attribute ක්ෂේත්‍රයේ :size අයිතම අඩංගු විය යුතුය.',
        'file' => ':attribute ක්ෂේත්‍රය :size කිලෝබයිට් විය යුතුය.',
        'numeric' => ':attribute ක්ෂේත්‍රය :size විය යුතුය.',
        'string' => ':attribute ක්ෂේත්‍රය :size අක්ෂර විය යුතුය.',
    ],
    'starts_with' => ':attribute ක්ෂේත්‍රය පහත සඳහන් වලින් එකක් ආරම්භ විය යුතුය: :values.',
    'string' => ':attribute ක්ෂේත්‍රය පේළියක් විය යුතුය.',
    'timezone' => ':attribute ක්ෂේත්‍රය වලංගු කාල කලාපයක් විය යුතුය.',
    'unique' => ':attribute දැනටමත් ගෙන ඇත.',
    'uploaded' => ':attribute උඩුගත කිරීමට අසමත් විය.',
    'uppercase' => ':attribute ක්ෂේත්‍රය ලොකු අකුරු විය යුතුය.',
    'url' => ':attribute ක්ෂේත්‍රය වලංගු URL එකක් විය යුතුය.',
    'ulid' => ':attribute ක්ෂේත්‍රය වලංගු ULID එකක් විය යුතුය.',
    'uuid' => ':attribute ක්ෂේත්‍රය වලංගු UUID එකක් විය යුතුය.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'අභිරුචි-පණිවිඩය',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];

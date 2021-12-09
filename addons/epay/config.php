<?php

return array(
    0 =>
        array(
            'name' => 'wechat',
            'title' => '微信',
            'type' => 'array',
            'content' =>
                array(),
            'value' =>
                array(
                    'app_id' => 'wx4685914d8cd836c8',
                    'appid' => 'wx4685914d8cd836c8',
                    'miniapp_id' => 'wx4685914d8cd836c8',
                    'app_secret' => '0edb9a677776990c703c4a9d30b9c176',
                    'mch_id' => '1615122354',
                    'key' => 'msjt1615122354161512235416151223',
                    'notify_url' => '/api/pay/notify/paytype/wechat',
                    'cert_client' => '/epay/certs/apiclient_cert.pem',
                    'cert_key' => '/epay/certs/apiclient_key.pem',
                    'log' => '1',
                ),
            'rule' => '',
            'msg' => '',
            'tip' => '微信参数配置',
            'ok' => '',
            'extend' => '',
        ),
    1 =>
        array(
            'name' => 'alipay',
            'title' => '支付宝',
            'type' => 'array',
            'content' =>
                array(),
            'value' =>
                array(
                    'app_id' => '2019091767477113',
                    'notify_url' => '/api/pay/notify/paytype/alipay',
                    'return_url' => '/api/pay/notify/type/alipay',
                    'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAiHuSX7X5vEu+SUTKkwN6iCJJLQoMmR0cyUfexrDvcpPndla0/bk7uGLsDkPcoCM+8vPAup6zGvxIiVR9Vqz91UUl1Gu96aH6BsII2tibt71sDxXASSSUGZatk6X6Ci6XEdTmPs4t5FgEx6SP/tC9xVtWSzKMlU/m3i9CWm9jPnE/nDtsyI8e15CwBsBJguixb9zd7mleZUGuHISqgFGAZ3Ok0eOAOMv1SB3x+J6HL5s84c/avV44O8XeGCND1BJKT4NJGwklTAW3aMld0xQParzrELamAYyh0X019nhTuiHIrlcb1Oj/nU9KEhnG8AW+soijvIZ2amSrj0bSjWL7SwIDAQAB',
                    'private_key' => 'MIIEogIBAAKCAQEAhjE+LTMix/37ZRWybzNoQi3AkdgD1NklK7iTAMFk4DLw0CtxNT2sTT2+2/vtNLdUAFAZAYCKIwANYihqUQwzxLHAHvVq4nds2tGlDY7KcovTcnbkWgVR2glrAzGabq6xLn/I838u87SZhRsh3CQHSUczI4JbRlvvEX6Ifs96YhQZLv1OdG8xgNtHUZQBaJ2EAAEHmRsXEycGdgV3Jrg6cRF074pPTzcmPtB8IoqsVyckaHIb8ykN7pgg1gBFFDENAxVzuP5Qv0FonXdqZOjWcYY/olAfcBBZUuXiK0Da3JYBE1l7801JEE/Mw67tlo0qze5dsrXTnZQ4iM3UN1KMHQIDAQABAoIBABabey8gO07YIh9AjYQgdAMaNCQd8V9Nie6YtONesYD39AwOFY9zqXCF436qLadA73zyAZR4NE17N8X+qxKmbJ5Jl+VfUTeACS3APf9PvBmaJvBUyVnFJaoHBilOlQbqlV0RGCi1l5JlLav4CzdMNpII1bu4vWnYGSmoWJSMncCiBPIrB/Poe295XGwuffksubfWIaxvn2sVKS0wNjH/GlDCPuO+XBeMNGf+tKSVHOIEUukWE8gRxCDVGA5gpoPfEgPEjqJeRO4hcR0gAhJLQjKr8Bx1hMILkuFNNmGOED3Gew8W/LZ9mc6V4by8O/HOf+bvX3IIXUfA2TEwRmMxPN0CgYEA2UcFf2FjELZN1PRqmO0feI6AosYp8eW8b1pXedPDc07WtkcitxAVhUBEpbE1A0kkl1hP5dqJ/kiX0E4jt5vx8llgLnRHMHo/LelPtENerwaUl1qhykQ+qdNCktigfZVeDmOxU4NohFNTX/5qTVSY+VMLGR5rdcLhn2pxK+gEjHsCgYEAnhuUzPFTDApdr3J+wNpmoQdD73EWwbz4+KsCJZ30+rQI/r66MCpZfFr22dI8YGLGONFSfP8ai/uTpJfj29m7ORw8NJ9UZE/lzBeHX6ObcIaaIlLRqeF/0QF0tnX5qNiaZqCtbQLXVjRtCIWZYR7Q+uOu+VSORaUiYWya2lux4kcCgYAPOIFzFfBpqEu1glXBVsMgn+L1BCrGDlDwAAUmvKXxevFhnYQaDN9lEUZ90Pckvsf4bjBSqseF77Hq3r5zy1Hcp1QsjOq7w6SPY8u4lguY+T4JiEjTMYquPOVqAhDkG0WWfndaoVb+BhROIjVyK134AVBCtzXR7w/9Kei5qaw/5wKBgDviCmvNAz1ON8mZvfRhQ/m9fLeVx569ajcU3g1NVFoYEkgaCP7xK090TIaXoBKJlrYyYeHB5VYbhQIUHVNJliW9UfhEWHxd3pV8W+OXXeoysPJLF+oV5IlO2du5t0OCNoikVssxBko6NQnDQCKOv2wnDECXDAPI1cq8jbIihK9FAoGAT5luYPDRiSDX9NNNPJi0r2OZLp0acI3s8y1iyoPIArvBSD+wlaGqSc5QWeA4qq3tt/BUq57ymDkgNfIG+cJYd7WH6yEB0/0/vBeLSUthgqR6OztZfZqJy+6kJ4jbgtEJlz0XBuqvDJRg6PacIsFWxohvdH+Wrm911VppL8loNVs=',
                    'log' => '1',
                ),
            'rule' => 'required',
            'msg' => '',
            'tip' => '支付宝参数配置',
            'ok' => '',
            'extend' => '',
        ),
    2 =>
        array(
            'name' => '__tips__',
            'title' => '温馨提示',
            'type' => 'array',
            'content' =>
                array(),
            'value' => '请注意微信支付证书路径位于/addons/epay/certs目录下，请替换成你自己的证书<br>appid：APP的appid<br>app_id：公众号的appid<br>app_secret：公众号的secret<br>miniapp_id：小程序ID<br>mch_id：微信商户ID<br>key：微信商户支付的密钥',
            'rule' => '',
            'msg' => '',
            'tip' => '微信参数配置',
            'ok' => '',
            'extend' => '',
        ),
);

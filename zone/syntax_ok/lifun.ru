$ORIGIN .
$TTL 14400  ; 4 hours
lifun.ru        IN SOA  ns1.lifun.ru. dns-admin.lifun.ru. (
                2009082401 ; serial
                14400      ; refresh (4 hours)
                3600       ; retry (1 hour)
                2592000    ; expire (4 weeks 2 days)
                600        ; minimum (10 minutes)
                )
            NS  ns1.lifun.ru.
            NS  ns1.beget.ru.
            A   81.222.198.165
            MX  10 mail.lifun.ru.
$ORIGIN lifun.ru.
*           A   81.222.198.165
localhost       A   127.0.0.1
ns1         A   81.222.198.162
ns2         A   81.222.131.99
www         CNAME   lifun.ru.

# ORA2GIT
შესანიშნავი მიდგომაა თუ თქვენ იყენებთ დომენურ სისტემას!

გითზე ინახება წინა ვერსია...

php სკრიპტი უნდა დასვათ cron ჯობზე:

*/1   *    *    *    *     curl -s -o /dev/null -l http://YourURL..../index.php

----------------------------------------------------------------------------------

არ დაგავიწყდეთ git -ზე  დაამატოთ უზერი    რომლითაც  დაპუშავთ და პროე პროექტი

.git/config - ის მაგალითი

[core]
        repositoryformatversion = 0
        filemode = true
        bare = false
        logallrefupdates = true
[remote "origin"]
        url = გითის ssh მისამართი
        fetch = +refs/heads/*:refs/remotes/origin/*
[branch "master"]
        remote = origin
        merge = refs/heads/master
[user]
        name =  gitora
        email = gitora@domain.com



ასევე დაგჭირდებათ ssh key -ის გენერირება აპაჩისთვის...
/etc/sudoers - ში ჩასამატებელი იქნება: apache ALL = NOPASSWD: /usr/bin/git
არ დაგავიწყდეთ დირექყორიის   chown -R apache:git /დირექტორია
და ასევე /etc/passwd - ში apaches - თვის /bin/bash - ის მითითება.

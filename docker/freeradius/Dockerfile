FROM freeradius/freeradius-server:3.2.10

COPY clients.conf /etc/raddb/clients.conf
COPY rest /etc/raddb/mods-enabled/rest
COPY portal /etc/raddb/sites-enabled/portal

RUN rm -f /etc/raddb/sites-enabled/default
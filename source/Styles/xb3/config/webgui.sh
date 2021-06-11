#! /bin/sh
##########################################################################
# If not stated otherwise in this file or this component's Licenses.txt
# file the following copyright and licenses apply:
#
# Copyright 2015 RDK Management
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
# http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
##########################################################################
#WEBGUI_SRC=/fss/gw/usr/www/html.tar.bz2
#WEBGUI_DEST=/var/www

source /etc/device.properties

if [ ! -f /nvram/certs/myrouter.io.cert.pem ] && [ "$BOX_TYPE" = "XB3" ]; then
    if [ -f /lib/rdk/check-webui-update.sh ]; then
        sh /lib/rdk/check-webui-update.sh
    fi
fi

#if test -f "$WEBGUI_SRC"
#then
#	if [ ! -d "$WEBGUI_DEST" ]; then
#		/bin/mkdir -p $WEBGUI_DEST
#	fi
#	/bin/tar xjf $WEBGUI_SRC -C $WEBGUI_DEST
#else
#	echo "WEBGUI SRC does not exist!"
#fi

# start lighttpd
source /etc/utopia/service.d/log_capture_path.sh
source /fss/gw/etc/utopia/service.d/log_env_var.sh
REVERT_FLAG="/nvram/reverted"

LIGHTTPD_PID=`pidof lighttpd`
if [ "$LIGHTTPD_PID" != "" ]; then
	/bin/kill $LIGHTTPD_PID
fi

HTTP_ADMIN_PORT=`syscfg get http_admin_port`
HTTP_PORT=`syscfg get mgmt_wan_httpport`
HTTP_PORT_ERT=`syscfg get mgmt_wan_httpport_ert`
HTTPS_PORT=`syscfg get mgmt_wan_httpsport`
BRIDGE_MODE=`syscfg get bridge_mode`

if [ "$BRIDGE_MODE" != "0" ]; then
    INTERFACE="lan0"
else
    INTERFACE="brlan0"
fi


cp /etc/lighttpd.conf /var
#sed -i "s/^server.port.*/server.port = $HTTP_PORT/" /var/lighttpd.conf
#sed -i "s#^\$SERVER\[.*\].*#\$SERVER[\"socket\"] == \":$HTTPS_PORT\" {#" /var/lighttpd.conf

HTTP_SECURITY_HEADER_ENABLE=`syscfg get HTTPSecurityHeaderEnable`

if [ "$HTTP_SECURITY_HEADER_ENABLE" = "true" ]; then
    echo "setenv.add-response-header = ("  >> /var/lighttpd.conf
    echo "    \"X-Frame-Options\" => \"deny\","  >> /var/lighttpd.conf
    echo "    \"X-XSS-Protection\" => \"1; mode=block\","  >> /var/lighttpd.conf
    echo "    \"X-Content-Type-Options\" => \"nosniff\","  >> /var/lighttpd.conf
    echo "    \"Content-Security-Policy\" => \"default-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline' 'unsafe-eval'; frame-src 'self' 'unsafe-inline' 'unsafe-eval'; font-src 'self' 'unsafe-inline' 'unsafe-eval'; form-action 'self' 'unsafe-inline' 'unsafe-eval'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; img-src 'self'; connect-src 'self'; object-src 'none'; media-src 'none'; script-nonce 'none'; plugin-types 'none'; reflected-xss 'none'; report-uri 'none';\","  >> /var/lighttpd.conf
    echo ")"  >> /var/lighttpd.conf
    echo "#sandbox 'allow-same-origin allow-scripts allow-popups allow-forms';"  >> /var/lighttpd.conf
fi

echo "server.port = $HTTP_ADMIN_PORT" >> /var/lighttpd.conf
echo "server.bind = \"$INTERFACE\"" >> /var/lighttpd.conf
echo "\$SERVER[\"socket\"] == \"wan0:80\" { server.use-ipv6 = \"enable\" }" >> /var/lighttpd.conf

if [ "x$HTTP_PORT_ERT" != "x" ] && [ $HTTP_PORT_ERT -ne 0 ] && [ "$HTTP_PORT_ERT" -ge 1025 ] && [ "$HTTP_PORT_ERT" -le 65535 ];then
    echo "\$SERVER[\"socket\"] == \"erouter0:$HTTP_PORT_ERT\" { server.use-ipv6 = \"enable\" }" >> /var/lighttpd.conf
else
    echo "\$SERVER[\"socket\"] == \"erouter0:$HTTP_PORT\" { server.use-ipv6 = \"enable\" }" >> /var/lighttpd.conf
fi

echo "\$SERVER[\"socket\"] == \"$INTERFACE:443\" { server.use-ipv6 = \"enable\" ssl.engine = \"enable\" ssl.pemfile = \"/etc/server.pem\" }" >> /var/lighttpd.conf
echo "\$SERVER[\"socket\"] == \"wan0:443\" { server.use-ipv6 = \"enable\" ssl.engine = \"enable\" ssl.pemfile = \"/etc/server.pem\" }" >> /var/lighttpd.conf
if [ $HTTPS_PORT -ne 0 ] && [ "$HTTPS_PORT" -ge 1025 ] && [ "$HTTPS_PORT" -le 65535 ]
then
    echo "\$SERVER[\"socket\"] == \"erouter0:$HTTPS_PORT\" { server.use-ipv6 = \"enable\" ssl.engine = \"enable\" ssl.pemfile = \"/etc/server.pem\" }" >> /var/lighttpd.conf
else
    # When the httpsport is set to NULL. Always put default value into database.
    syscfg set mgmt_wan_httpsport 8081
    syscfg commit
    HTTPS_PORT=`syscfg get mgmt_wan_httpsport`
    echo "\$SERVER[\"socket\"] == \"erouter0:$HTTPS_PORT\" { server.use-ipv6 = \"enable\" ssl.engine = \"enable\" ssl.pemfile = \"/etc/server.pem\" }" >> /var/lighttpd.conf
fi

 
WIFIUNCONFIGURED=`syscfg get redirection_flag`
SET_CONFIGURE_FLAG=`psmcli get eRT.com.cisco.spvtg.ccsp.Device.WiFi.NotifyWiFiChanges`

#Read the http response value
NETWORKRESPONSEVALUE=`cat /var/tmp/networkresponse.txt`

iter=0
max_iter=2
while [ "$SET_CONFIGURE_FLAG" = "" ] && [ "$iter" -le $max_iter ]
do
	iter=$((iter+1))
	echo "$iter"
	SET_CONFIGURE_FLAG=`psmcli get eRT.com.cisco.spvtg.ccsp.Device.WiFi.NotifyWiFiChanges`
done
echo "WEBGUI : NotifyWiFiChanges is $SET_CONFIGURE_FLAG"
echo "WEBGUI : redirection_flag val is $WIFIUNCONFIGURED"
if [ "$WIFIUNCONFIGURED" = "true" ]
then
	if [ "$NETWORKRESPONSEVALUE" = "204" ] && [ "$SET_CONFIGURE_FLAG" = "true" ]
	then
		while : ; do
		echo "WEBGUI : Waiting for PandM to initalize completely to set ConfigureWiFi flag"
		CHECK_PAM_INITIALIZED=`find /tmp/ -name "pam_initialized"`
		echo "CHECK_PAM_INITIALIZED is $CHECK_PAM_INITIALIZED"
  	        	if [ "$CHECK_PAM_INITIALIZED" != "" ]
   			then
			   echo "WEBGUI : WiFi is not configured, setting ConfigureWiFi to true"
	         	   output=`dmcli eRT setvalues Device.DeviceInfo.X_RDKCENTRAL-COM_ConfigureWiFi bool TRUE`
			   check_success=`echo $output | grep  "Execution succeed."`
  	        		if [ "$check_success" != "" ]
   				then
     			 	   echo "WEBGUI : Setting ConfigureWiFi to true is success"
 	       			fi
      			   break
 	       		fi
		sleep 2
		done
	

	else
		echo "WEBGUI : WiFi is already configured"
		if [ ! -e "$REVERT_FLAG" ] && [ "$NETWORKRESPONSEVALUE" = "204" ]
		then
			echo "WEBGUI: WiFi is already configured. Set reverted flag in nvram"	
			touch $REVERT_FLAG
		fi
	fi
fi		


#echo "\$SERVER[\"socket\"] == \"$INTERFACE:10443\" { server.use-ipv6 = \"enable\" ssl.engine = \"enable\" ssl.pemfile = \"/etc/server.pem\" server.document-root = \"/fss/gw/usr/walled_garden/parcon/siteblk\" server.error-handler-404 = \"/index.php\" }" >> /var/lighttpd.conf
#echo "\$SERVER[\"socket\"] == \"$INTERFACE:18080\" { server.use-ipv6 = \"enable\"  server.document-root = \"/fss/gw/usr/walled_garden/parcon/siteblk\" server.error-handler-404 = \"/index.php\" }" >> /var/lighttpd.conf

LOG_PATH_OLD="/var/tmp/logs/"

if [ "$LOG_PATH_OLD" != "$LOG_PATH" ]
then
	sed -i "s|${LOG_PATH_OLD}|${LOG_PATH}|g" /var/lighttpd.conf
fi

LD_LIBRARY_PATH=/fss/gw/usr/ccsp:$LD_LIBRARY_PATH lighttpd -f /var/lighttpd.conf

echo "WEBGUI : Set event"
sysevent set webserver started

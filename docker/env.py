#!/usr/bin/env python

import socket
import subprocess
import sys

remoteServer    = "127.0.0.1"
remoteServerIP  = socket.gethostbyname(remoteServer)

unusedPort = []

for port in range(10000,65535):  
  sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
  result = sock.connect_ex((remoteServerIP, port))
  if result == 0:
    sock.close()
  else:
    unusedPort.append(str(port))
  if len(unusedPort) == 5:
      break

varArray = ["NGINX_HTTP_PORT", "NGINX_HTTPS_PORT", "MYSQL_PORT", "PHPMYADMIN_PORT", "REDIS_PORT"]

network_name = "lumen_" + "_".join(unusedPort)
subprocess.call("docker network rm %s" % network_name, shell=True)
subprocess.call("docker network create -d bridge %s" % network_name, shell=True)

fd = open("./.env", "w")
#env_config = []

for i in range(len(varArray)):
  row = "%s=%s\n" % (varArray[i], unusedPort[i])
  fd.write(row)
  #env_config.append(row)

#env_config.append("%s=%s" % ("USER_NETWORK", network_name))
fd.write("%s=%s\n" % ("USER_NETWORK", network_name))
fd.close()

<?php
/*
 * interfaces_assign.php
 *
 * part of pfSense (https://www.pfsense.org)
 * Copyright (c) 2004-2013 BSD Perimeter
 * Copyright (c) 2013-2016 Electric Sheep Fencing
 * Copyright (c) 2014-2024 Rubicon Communications, LLC (Netgate)
 * All rights reserved.
 *
 * originally based on m0n0wall (http://m0n0.ch/wall)
 * Copyright (c) 2003-2004 Manuel Kasper <mk@neon1.net>.
 * Written by Jim McBeath based on existing m0n0wall files
 * All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

##|+PRIV
##|*IDENT=page-interfaces-assignnetworkports
##|*NAME=Interfaces: Interface Assignments
##|*DESCR=Allow access to the 'Interfaces: Interface Assignments' page.
##|*MATCH=interfaces_assign.php*
##|-PRIV

//$timealla = microtime(true);




$pgtitle = array(gettext("Interfaces"), gettext("Packet Analyzer"));
$shortcut_section = "interfaces";

require_once("guiconfig.inc");
require_once("functions.inc");
require_once("filter.inc");
require_once("shaper.inc");
require_once("ipsec.inc");
require_once("vpn.inc");
require_once("openvpn.inc");
require_once("captiveportal.inc");
require_once("rrd.inc");
require_once("interfaces_fast.inc");

global $friendlyifnames;

global $config;

/*moved most gettext calls to here, we really don't want to be repeatedly calling gettext() within loops if it can be avoided.*/
$gettextArray = array('add'=>gettext('Add'),'addif'=>gettext('Add interface'),'delete'=>gettext('Delete'),'deleteif'=>gettext('Delete interface'),'edit'=>gettext('Edit'),'on'=>gettext('on'));

/*
	In this file, "port" refers to the physical port name,
	while "interface" refers to LAN, WAN, or OPTn.
*/

/* get list without VLAN interfaces */
$portlist = get_interface_list();

/*another *_fast function from interfaces_fast.inc. These functions are basically the same as the
ones they're named after, except they (usually) take an array and (always) return an array. This means that they only
need to be called once per script run, the returned array contains all the data necessary for repeated use */
$friendlyifnames = convert_real_interface_to_friendly_interface_name_fast();


global $ipsec_descrs;
$ipsec_descrs = interface_ipsec_vti_list_all();
foreach ($ipsec_descrs as $ifname => $ifdescr) {
	$portlist[$ifname] = array('descr' => $ifdescr);
}


$ifdescrs = interface_assign_description_fast($portlist,$friendlyifnames);



/* Create a list of unused ports */
$unused_portlist = array();
$portArray = array_keys($portlist);

$ifaceArray = array_column($config['interfaces'],'if');
$unused = array_diff($portArray,$ifaceArray);
$unused = array_flip($unused);
$unused_portlist = array_intersect_key($portlist,$unused);//*/
unset($unused,$portArray,$ifaceArray);

include("head.inc");


$portselect='';
?>

<?php
$statusPath = '/usr/local/www/sertifika/serviceStatus.txt'; // Kullanacağınız dosyanın adını buraya ekleyin

if (file_exists($statusPath)) {
    $getStatus = file_get_contents($statusPath);
} else {
    $getStatus = 'Dosya bulunamadı';
}
?>

<form method="post" action="execute_ccode.php">
    <label for="interfaceNetwork">interface Seçimi:</label>
    <select name="interfaceNetwork" id="interfaceNetwork">
        <?php
        foreach ($portlist as $portname => $portinfo) {
            echo '<option value="' . $portname . '">' . $ifdescrs[$portname] . '</option>';
        }
        ?>
    </select>

    <div id="portsContainer">
            <div class="portEntry">
                <label for="portInput">Port Numarası:</label>
                <input type="text" class="portInput" name="ports[]" placeholder="Örn: 80">
                
                <label for="protocolSelect">Protokol:</label>
                <select class="protocolSelect" name="protocols[]">
                    <option value="tcp">TCP</option>
                    <option value="udp">UDP</option>
                </select>
            </div>
        </div>

        <button type="button" onclick="addPortEntry()">Port Ekle</button>
        <button type="button" id="stopButton" onclick="stopAnalyzer()">Durdur</button>
        <p><?php echo htmlspecialchars($getStatus); ?></p>

    <input type="submit" value="Başlat">
</form>

<script>

        function stopAnalyzer() {
        // Durdur butonuna tıklandığında yapılacak işlemler
        window.location.href = "stop_analyzer.php";
        }

        function addPortEntry() {
            var container = document.getElementById('portsContainer');
            
            // Yeni port entry div'i oluştur
            var newPortEntry = document.createElement('div');
            newPortEntry.className = 'portEntry';
            
            // Port numarası için input elemanı oluştur
            var newPortInput = document.createElement('input');
            newPortInput.type = 'text';
            newPortInput.className = 'portInput';
            newPortInput.name = 'ports[]';
            newPortInput.placeholder = 'Örn: 80';
            
            // Protokol için select elemanı oluştur
            var newProtocolSelect = document.createElement('select');
            newProtocolSelect.className = 'protocolSelect';
            newProtocolSelect.name = 'protocols[]';
            
            // TCP seçeneği oluştur
            var tcpOption = document.createElement('option');
            tcpOption.value = 'tcp';
            tcpOption.text = 'TCP';
            newProtocolSelect.appendChild(tcpOption);
            
            // UDP seçeneği oluştur
            var udpOption = document.createElement('option');
            udpOption.value = 'udp';
            udpOption.text = 'UDP';
            newProtocolSelect.appendChild(udpOption);
            
            // Yeni div içine elemanları ekle
            newPortEntry.appendChild(newPortInput);
            newPortEntry.appendChild(document.createTextNode(' ')); // Boşluk ekleyerek aralarında boşluk bırak
            newPortEntry.appendChild(newProtocolSelect);
            
            // Container'a yeni port entry div'ini ekle
            container.appendChild(newPortEntry);
        }
    </script>




<?php
print_info_box(gettext("Interfaces that are configured as members of a lagg(4) interface will not be shown.") .
    '<br/><br/>' .
    gettext("Wireless interfaces must be created on the Wireless tab before they can be assigned."), 'info', false);
?>

<?php include("foot.inc")?>

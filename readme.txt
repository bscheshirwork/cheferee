�������� ������� �� ������� PHP Developer (wargaming)
�����������, ��� �� ���������� ������� � ���������� ���������� ����������� ������� �� �������� �� ����������� �������. ��� ���� ���� ������ - ����������� ��������� ��� �����, �� ���� ������ ����������:
1. ���� ������ ����������, � ��������� �� ELO ��������
2. ����� �������� ��������� �������/��������� ����������.
3. ������ ���������� ����, �� �������� ����������� �������
4. ���� ����������� ������ ������ (������=1, �����=0.5, ���������=0).
5. ��� ����� ����� �� ������ ����� ����� ���-�� �������, ������ �������������.
6. ���������� �������� ELO �� ��������� ����������� �������. 

������������ ����������:
1. ��������/���������� YII (http://www.yiiframework.com/);
2. ���������� �� ������� (��������) �� ������� ���� ����� ���������� (���� ������� ���� � ����������, �.�. �� ������ ������������� ���� �������� ����� web-�������) ���, ����� ��� ������ ����
$yii=dirname(__FILE__).'/../../framework/yii.php' � index.php; (� ����������� � /protected/yiic.php, /protected/cron.php);
3. ������� ���� ������ �� ����� ������� (��� �� ��������)
4. ������ ��������� � ������� '/protected/config/main.php', '/protected/config/console.php' � '/protected/config/cron.php' ������ ��������� ����������� � ���� ������;
5. ��������� ������������ �� MySQL �� ����� ����� 
'/protected/data/schema.mysql.sql ����� ����� ������:
5.1. ��������� ����������� ������� ���� phpMyAdmin ���
5.2. ��������� ���������� �������� �������� ��� ������ - �� ������� ��������� "php ����_�_�������/protected/yiic migrate".
6. ���������� ���������. ��� �������� �������� ������������ admin c ������� admin. ����� ��������� ������� ������ � �������� �������������-�����, ��� ������� �������� ������������ ����������������. ���������������� ������������� �������� �������� ����� � �������.

���������������� ������ �����:
������ ������� ���������� ����� ���������� �� 
http://bscheshir.16mb.com/cheferee/
���� ���� ��� � ��� ��������� ��������, ����������������� ���� �� ��������� ����� ���������. (�.�. �� ���������� �������� ����������� ����������, cron ��������� php ���� ���������� ����������:
<?php
$_SERVER['argv']=Array($_SERVER['argv'][0],'remigrateall');
require_once(dirname(__FILE__). '/public_html/cheferee/protected/cron.php');
)

�����������:
��������� YII � �������� bootstrap ���� �� ���� ����������� ������ MVC, ���� ����������, CRUD ���������������� ��� ������ ������������� � �������, ����� ������ ����������.
������ ������������� �� �����, � ����������� ����������� ����������� � GridController. ����������� ���������� ��� ����� � ��� ��� - ��� �������������.
������ ����������� � /protected/models, ������������� -  � /themes/bootstrap/views. 
����������� ������� ���������� - ������������, ������� ���������, ������������� �������� � ������� �����������; ������� ���� ��������, �������� ���������� ������� � �������� ��������.
����������������. ����������� ���������� �������� ���������� �����������, �������� ��������� �����������.

���������, ������� �������� �� ����� ����������:
1. ���������� � �����. ������� - �������, ������� ������ ����, ������ ����������� ������ �  ��������� Ubuntu server 13.10. ��� �������, �������������� �� ��� LAMP, ssh, ftp.
3. �������� ��������������� ���������� �� ���� � �������� ����������. ������� - ��������������.

������� �� �������� � ���� �����������.
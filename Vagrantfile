# -*- mode: ruby -*-:
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  config.vm.define :main do |main_config|
    main_config.vm.box = "precise64"
    main_config.vm.box_url = "http://files.vagrantup.com/precise64.box"
    main_config.vm.network "private_network", ip: "192.168.19.111"
    main_config.vm.host_name = "cube.loc"
    main_config.vm.synced_folder ".", "/vagrant", type: "nfs"
    main_config.vm.provision :shell, :path => "scripts/provision/provision.sh"
    if Vagrant.has_plugin?("vagrant-bindfs")
      main_config.bindfs.bind_folder "/vagrant", "/vagrant",
        :group => "www-data",
        :perms => "u=rwX:g=rwX:o=rD"
    end
  end
  config.vm.provider "virtualbox" do |v|
    v.customize ["modifyvm", :id, "--memory", "2048"]
    v.customize ["modifyvm", :id, "--cpus", "2"]
  end
  if Vagrant.has_plugin?("HostManager")
    config.hostmanager.enabled = true
    config.hostmanager.manage_host = true
    config.hostmanager.ignore_private_ip = false
  end
end

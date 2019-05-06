1. Copy the content of the repository in the automation folder of your project
2. Choose a Vagrantfile template from the vagrant folder depending on your needs: with/without Samba, with a new app or 
with an already existing one to be installed etc. Copy the template in your automation folder and keep it as a sample
for the project. Rename it to Vagrantfile when it's ready to be used
3. If you install an already existing application, copy your personal SSH key (id_rsa) with access to your git account 
to automation/deploy/files/local
4. Run "vagrant up" from the automation folder
5. If you use Samba, you can access the project files at \\{YOUR_VM_IP}. The default credentials are vagrant/vagrant
6. If you want to install a fresh Symfony app, add in the Ansible vars for your app the following:
   symfony:
     version: 3.3
   You are free to decide the version.
7. Remove the vagrant folder, .gitignore and README.md. Copy the content of .gitignore.template to your project's 
.gitignore. Remove .gitignore.template afterwards.

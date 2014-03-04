#!/usr/bin/perl
use strict;
use warnings;

#Lewis Deacon
#26/02/14
#Topic sorting to a format of Topic 0 = a,b,c,d,etc.
#To run ./SortTopics inputCsvFile OutputFile

#Initialise some variables that are used throughout
my $file = $ARGV[0] or die "Need to get CSV file on the command line\n";
my $outputFile = $ARGV[1] or die "Need to get txt file on the command line\n";
my $sum = "";
my $prefix = "Topic ";
my $temp = 0;
my $id =0;
my $count =0;

#Open the input file for reading and the output file for writing
open(my $data, '<', $file) or die "Could not open '$file' $!\n";
open(my $fh, '>', $outputFile) or die "Could not open file '$outputFile' $!";

#While there is a next line keep reading
while (my $line = <$data>) {
  chomp $line;
  #Store the values seperated by a comma in an array
  my @fields = split "," , $line;
  #If the lines first field is equal to the current id then go here 
  if($fields[0] == $id){
  #Current ID is the first one in the file
  $id = $fields[0];
  #So there isnt a comma on the very first output line
  if($count ==0){
	$sum = $fields[1];  
  }
  #Add the topic to the current ID
  $sum = $sum.",".$fields[1];
  $count = $count + 1;
  #If the next lines field[0] is different from id then increment the id
  #and start a new $sum
}else{
	#Add the prefix so it takes form of Topic i = "";
	$sum = $prefix.$id." = \"".$sum."\"";
	#Print the topic to file
    print $fh "$sum\n";
    #Increment the ID for the next topic
	$id = $id +1;
	#Reset the $sum to equal the next topic word so none get missed out
	$sum = $fields[1];
	
}

}
close $fh;

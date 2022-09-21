FILENAME=$1;

# Extract the phpdoc blocks
sed -n -e '/\/\*\*/,/\*\// p' $FILENAME | 
# Remove spaces before the * of the comment blocks that are left beind
sed 's/^ *//g' | 
# and remove the space after the * part of the comment 
sed 's/^\* //g' |
# Remove the top of the doc block (/**)
sed 's/^\/\*\*$//g' |
# And the bottom part (*/)
sed 's/\*\/$//g' | 
# Replace @method something by ### so the method name is encapsulated
#sed 's/^@method \(.*\)/## ``\1``/g' |
sed 's:^@method \([a-zA-Z0-9_-]*\)\(.*\):\n---\n## \1()\n@prototype \1\2:g' |
# Start working on the parameter block: Add the totle
sed 's/@parameters/\n### Parameters\n/g' | 
# Add a new line after the end of the block and close the code block.
sed 's/\(@prototype.*\))/\1 \n)\n```\n/g' | 
# Remove spaces between commas
sed '/@prototype.*/s/, /,/g'| 
#Split the arguments into newlines
sed '/@prototype.*/s/,/,\n\t/g' | 
# Add a newline after the first bracket after the function
sed '/^@prototype/s/(/&\n\t/g' | 
# Add a line between the Prototype section and the code prototype and add it as a code block.
sed 's/@prototype/\n### Description\n```\n/g' |
# Set all code variables to monotype:
#sed 's:\$\([^ ]*\):\``$\1``:g'
sed '/^@param/s/\$\([^ ]*\)/\``$\1``/g' |
# Now convert @param to an unsigned list
sed 's/^@param/-/g' |
# Lets do the class stuff now
sed 's/@class/# Class/g' |
# And properties
sed 's/@properties/\n## Properties/g' |
# Quote the return value if present
sed 's/@return \(.*\)/\n### Return ``\1``/g'
# sed 's/^@return/### Return/g';

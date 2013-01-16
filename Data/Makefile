export utildir

SUBDIR = $(filter-out Makefile, $(wildcard */))

include $(utildir)/PubRule

$(pubdir)/$(appname)/data/Init.csv: Init.csv $(pubdir)/$(appname)/data
	cp $< $@

$(pubdir)/$(appname)/Data:
	mkdir -p $@

$(pubdir)/$(appname)/Data/Init.csv: Init.csv $(pubdir)/$(appname)/Data
	cp $< $@

publish: $(pubdir)/$(appname)/Data/Init.csv

DISTFILES = $(SUBDIR) Makefile
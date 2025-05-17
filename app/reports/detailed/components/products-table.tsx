"use client"

import { useState } from "react"
import {
  type ColumnDef,
  type ColumnFiltersState,
  type SortingState,
  flexRender,
  getCoreRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useReactTable,
} from "@tanstack/react-table"
import { ArrowUpDown, ChevronDown } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Skeleton } from "@/components/ui/skeleton"

interface ProductsData {
  id: string
  name: string
  sku: string
  category: string
  website: string
  price: number
  stock: number
  sold: number
  revenue: number
}

const data: ProductsData[] = [
  {
    id: "1",
    name: "iPhone 13 Pro Max",
    sku: "IP-13PM-256",
    category: "Điện thoại",
    website: "Shop Điện Tử",
    price: 28500000,
    stock: 15,
    sold: 28,
    revenue: 798000000,
  },
  {
    id: "2",
    name: "Áo sơ mi nam dài tay",
    sku: "ASM-NAM-L",
    category: "Thời trang nam",
    website: "Shop Thời Trang",
    price: 450000,
    stock: 45,
    sold: 32,
    revenue: 14400000,
  },
  {
    id: "3",
    name: "Kem chống nắng Anessa",
    sku: "KCN-ANESSA",
    category: "Mỹ phẩm",
    website: "Shop Mỹ Phẩm",
    price: 550000,
    stock: 28,
    sold: 45,
    revenue: 24750000,
  },
  {
    id: "4",
    name: "Samsung Galaxy S22 Ultra",
    sku: "SS-S22U-256",
    category: "Điện thoại",
    website: "Shop Điện Tử",
    price: 25500000,
    stock: 12,
    sold: 18,
    revenue: 459000000,
  },
  {
    id: "5",
    name: "Váy đầm nữ dáng xòe",
    sku: "VD-NU-XOE",
    category: "Thời trang nữ",
    website: "Shop Thời Trang",
    price: 650000,
    stock: 35,
    sold: 28,
    revenue: 18200000,
  },
  {
    id: "6",
    name: "Son môi Dior",
    sku: "SM-DIOR",
    category: "Mỹ phẩm",
    website: "Shop Mỹ Phẩm",
    price: 850000,
    stock: 20,
    sold: 32,
    revenue: 27200000,
  },
  {
    id: "7",
    name: "Laptop Dell XPS 13",
    sku: "LT-DELL-XPS13",
    category: "Laptop",
    website: "Shop Điện Tử",
    price: 32500000,
    stock: 8,
    sold: 12,
    revenue: 390000000,
  },
  {
    id: "8",
    name: "Quần jean nam slim fit",
    sku: "QJ-NAM-SF",
    category: "Thời trang nam",
    website: "Shop Thời Trang",
    price: 550000,
    stock: 40,
    sold: 25,
    revenue: 13750000,
  },
  {
    id: "9",
    name: "Serum dưỡng da SK-II",
    sku: "SR-SKII",
    category: "Mỹ phẩm",
    website: "Shop Mỹ Phẩm",
    price: 2850000,
    stock: 15,
    sold: 18,
    revenue: 51300000,
  },
  {
    id: "10",
    name: "iPad Pro 12.9 inch",
    sku: "IP-PRO-129",
    category: "Máy tính bảng",
    website: "Shop Điện Tử",
    price: 24500000,
    stock: 10,
    sold: 15,
    revenue: 367500000,
  },
]

const columns: ColumnDef<ProductsData>[] = [
  {
    accessorKey: "name",
    header: "Tên sản phẩm",
    cell: ({ row }) => <div className="font-medium">{row.getValue("name")}</div>,
  },
  {
    accessorKey: "sku",
    header: "SKU",
    cell: ({ row }) => <div>{row.getValue("sku")}</div>,
  },
  {
    accessorKey: "category",
    header: "Danh mục",
    cell: ({ row }) => <div>{row.getValue("category")}</div>,
  },
  {
    accessorKey: "website",
    header: "Website",
    cell: ({ row }) => <div>{row.getValue("website")}</div>,
  },
  {
    accessorKey: "price",
    header: ({ column }) => {
      return (
        <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}>
          Giá
          <ArrowUpDown className="ml-2 h-4 w-4" />
        </Button>
      )
    },
    cell: ({ row }) => {
      const amount = Number.parseFloat(row.getValue("price"))
      const formatted = new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
        maximumFractionDigits: 0,
      }).format(amount)

      return <div className="text-right">{formatted}</div>
    },
  },
  {
    accessorKey: "stock",
    header: "Tồn kho",
    cell: ({ row }) => <div className="text-right">{row.getValue("stock")}</div>,
  },
  {
    accessorKey: "sold",
    header: ({ column }) => {
      return (
        <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}>
          Đã bán
          <ArrowUpDown className="ml-2 h-4 w-4" />
        </Button>
      )
    },
    cell: ({ row }) => <div className="text-right">{row.getValue("sold")}</div>,
  },
  {
    accessorKey: "revenue",
    header: ({ column }) => {
      return (
        <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}>
          Doanh thu
          <ArrowUpDown className="ml-2 h-4 w-4" />
        </Button>
      )
    },
    cell: ({ row }) => {
      const amount = Number.parseFloat(row.getValue("revenue"))
      const formatted = new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
        maximumFractionDigits: 0,
      }).format(amount)

      return <div className="text-right font-medium">{formatted}</div>
    },
  },
]

interface ProductsTableProps {
  isLoading?: boolean
}

export function ProductsTable({ isLoading = false }: ProductsTableProps) {
  const [sorting, setSorting] = useState<SortingState>([])
  const [columnFilters, setColumnFilters] = useState<ColumnFiltersState>([])
  const [columnVisibility, setColumnVisibility] = useState({})

  const table = useReactTable({
    data,
    columns,
    onSortingChange: setSorting,
    onColumnFiltersChange: setColumnFilters,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    onColumnVisibilityChange: setColumnVisibility,
    state: {
      sorting,
      columnFilters,
      columnVisibility,
    },
  })

  if (isLoading) {
    return (
      <div className="w-full">
        <div className="flex items-center py-4">
          <Skeleton className="h-10 w-[250px]" />
          <Skeleton className="h-10 w-[110px] ml-auto" />
        </div>
        <div className="rounded-md border">
          <Skeleton className="h-[400px] w-full" />
        </div>
        <div className="flex items-center justify-end space-x-2 py-4">
          <Skeleton className="h-10 w-[100px]" />
          <Skeleton className="h-10 w-[70px]" />
        </div>
      </div>
    )
  }

  return (
    <div className="w-full">
      <div className="flex items-center py-4">
        <Input
          placeholder="Tìm kiếm theo tên sản phẩm..."
          value={(table.getColumn("name")?.getFilterValue() as string) ?? ""}
          onChange={(event) => table.getColumn("name")?.setFilterValue(event.target.value)}
          className="max-w-sm"
        />
        <DropdownMenu>
          <DropdownMenuTrigger asChild>
            <Button variant="outline" className="ml-auto">
              Cột <ChevronDown className="ml-2 h-4 w-4" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end">
            {table
              .getAllColumns()
              .filter((column) => column.getCanHide())
              .map((column) => {
                return (
                  <DropdownMenuCheckboxItem
                    key={column.id}
                    className="capitalize"
                    checked={column.getIsVisible()}
                    onCheckedChange={(value) => column.toggleVisibility(!!value)}
                  >
                    {column.id === "name"
                      ? "Tên sản phẩm"
                      : column.id === "sku"
                        ? "SKU"
                        : column.id === "category"
                          ? "Danh mục"
                          : column.id === "website"
                            ? "Website"
                            : column.id === "price"
                              ? "Giá"
                              : column.id === "stock"
                                ? "Tồn kho"
                                : column.id === "sold"
                                  ? "Đã bán"
                                  : column.id === "revenue"
                                    ? "Doanh thu"
                                    : column.id}
                  </DropdownMenuCheckboxItem>
                )
              })}
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
      <div className="rounded-md border">
        <Table>
          <TableHeader>
            {table.getHeaderGroups().map((headerGroup) => (
              <TableRow key={headerGroup.id}>
                {headerGroup.headers.map((header) => {
                  return (
                    <TableHead key={header.id}>
                      {header.isPlaceholder ? null : flexRender(header.column.columnDef.header, header.getContext())}
                    </TableHead>
                  )
                })}
              </TableRow>
            ))}
          </TableHeader>
          <TableBody>
            {table.getRowModel().rows?.length ? (
              table.getRowModel().rows.map((row) => (
                <TableRow key={row.id} data-state={row.getIsSelected() && "selected"}>
                  {row.getVisibleCells().map((cell) => (
                    <TableCell key={cell.id}>{flexRender(cell.column.columnDef.cell, cell.getContext())}</TableCell>
                  ))}
                </TableRow>
              ))
            ) : (
              <TableRow>
                <TableCell colSpan={columns.length} className="h-24 text-center">
                  Không tìm thấy kết quả.
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </div>
      <div className="flex items-center justify-end space-x-2 py-4">
        <div className="flex-1 text-sm text-muted-foreground">
          Hiển thị {table.getRowModel().rows.length} trong tổng số {data.length} dòng.
        </div>
        <div className="space-x-2">
          <Button
            variant="outline"
            size="sm"
            onClick={() => table.previousPage()}
            disabled={!table.getCanPreviousPage()}
          >
            Trước
          </Button>
          <Button variant="outline" size="sm" onClick={() => table.nextPage()} disabled={!table.getCanNextPage()}>
            Sau
          </Button>
        </div>
      </div>
    </div>
  )
}
